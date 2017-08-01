<?php
/**
 *
 *
 */

namespace ApigilityTools\Mapper;

use ApigilityTools\Rest\Entity\EventAwareEntity;
use Interop\Container\ContainerInterface;
use MessageExchangeEventManager\Event\Event;
use MessageExchangeEventManager\Request\Request;
use MessageExchangeEventManager\Response\Response;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\TableIdentifier;
use Zend\Filter\Word\UnderscoreToCamelCase;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Stdlib\ArrayUtils;

/**
 * Class DefaultTableGatewayMapper
 * espone metodi per l'interazione con la base dati con comportamenti standard
 *
 * si possono modificare i comportamenti dei metodi in override sulle classi estese
 * oppure alterare i select intercettando gli eventi
 *
 * @package ApigilityTools
 */
class MapperFactory
    implements FactoryInterface
{

    /**
     * @param \Interop\Container\ContainerInterface $container
     * @param \Zend\Db\Adapter\Adapter              $dbAdapter
     * @param                                       $table
     * @param                                       $schema
     * @param                                       $mapperClass
     * @param                                       $controllerClass
     * @param                                       $entityClass
     * @param                                       $collectionClass
     * @param null                                  $resultset
     * @param null                                  $hydrator
     *
     * @return \ApigilityTools\Mapper\Mapper
     */
    public static function mapperFactory(ContainerInterface $container, Adapter $dbAdapter, $table, $schema,
    $mapperClass, $controllerClass, $entityClass, $collectionClass, $resultset = null, $hydrator = null)
    {

        $event = self::getEvent($container);

        /**
         * @var $mapper \ApigilityTools\Mapper\Mapper
         *
         */
        $mapper = new $mapperClass($event);
        $event->getRequest()->getParameters()->set('mapper', $mapper);

        if (!$mapper instanceof Mapper) {
            throw new ServiceNotCreatedException('$mapperClass must be instance of ApigilityTools\Mapper\SqlActuatorMapper '. $mapperClass.' given',
                                                 500);
        }

        $config = $container->get('Config');

        $halConfig = $config['zf-hal']['metadata_map'];
        $apigilityConfig = $config['zf-rest'][$controllerClass];

        self::setSql($dbAdapter, $table, $schema, $event);

        self::setEntityClass($entityClass, $event);
        self::setCollectionClass($collectionClass, $event);
        self::setHalConfig($entityClass, $collectionClass, $halConfig, $event);
        self::setApigilityConfig($apigilityConfig, $event);

        self::setHydrator($hydrator, $container, $event);
        self::setResultset($resultset, $container, $event);

        return $mapper;

    }

    /**
     * @param \Zend\Db\Adapter\Adapter $dbAdapter
     * @param                          $table
     * @param                          $schema
     * @param                          $event
     */
    private static function setSql(Adapter $dbAdapter, $table, $schema, Event $event)
    {
        $sql = new Sql($dbAdapter, new TableIdentifier($table, $schema));
        $event->getRequest()->getParameters()->set('sql', $sql);
    }

    /**
     * @param $entityClass
     * @param $event
     */
    private static function setEntityClass($entityClass, Event $event)
    {
        $event->getRequest()->getParameters()->set('entityClass', $entityClass);
    }

    /**
     * @param $entityClass
     * @param $collectionClass
     * @param $event
     * @param $metadataMap
     */
    private static function setHalConfig($entityClass, $collectionClass, $metadataMap, Event $event)
    {
        $halConfig = [
            $entityClass     => $metadataMap[$entityClass],
            $collectionClass => $metadataMap[$collectionClass],
        ];
        $event->getRequest()->getParameters()->set('halConfig', $halConfig);
        $event->getRequest()->getParameters()->set('identifierName',
                                                   $halConfig[$entityClass]['entity_identifier_name'])
        ;
    }

    /**
     * @param                                          $apigilityConfig
     * @param \MessageExchangeEventManager\Event\Event $event
     *
     * @internal param $entityClass
     * @internal param $collectionClass
     * @internal param $metadataMap
     */
    private static function setApigilityConfig($apigilityConfig, Event $event)
    {
        $event->getRequest()->getParameters()->set('apigilityConfig', $apigilityConfig);
    }

    /**
     * @param $collectionClass
     * @param $event
     */
    private static function setCollectionClass($collectionClass, Event $event)
    {
        $event->getRequest()->getParameters()->set('collectionClass', $collectionClass);
    }

    /**
     * l'utilizzo della factory invocandola, prevede la compilazione di adeguata configurazione
     * sotto la chiave ['apigility-tools']['sql-actuator-mapper']
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param string                                $requestedName
     * @param array|null                            $options
     *
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('Config');
        $sqlActuatorMapperConfig = $config['apigility-tools']['sql-actuator-mapper'];
        $halMetadataMapConfig = $config['zf-hal']['metadata_map'];

        $requestedConfig = $this->getRequestedConfig($requestedName, $sqlActuatorMapperConfig);

        $namespace=$this->extractConfigParam($requestedConfig, 'namespace');

        $controllerClass = $namespace . '\Controller';
        $controllerConfig=$config['zf-rest'][$controllerClass];
        $entityClass = $controllerConfig['entity_class'];
        $collectionClass = $controllerConfig['collection_class'];
        $hydratorClass = $halMetadataMapConfig[$entityClass]['hydrator'];

        $dbAdapter = $container->get($this->extractConfigParam($requestedConfig, 'db_adapter'));
        $dbSchema = $this->extractConfigParam($requestedConfig, 'db_schema');
        $dbTable = $this->extractConfigParam($requestedConfig, 'db_table');
        $mapperClass =$this->extractConfigParam($requestedConfig, 'mapper_class');

        $entity = $container->get($entityClass);
        if (!$entity instanceof EventAwareEntity) {
            throw new ServiceNotCreatedException('Entity must be instance of EventAwareEntity', 500);
        }

        $hydrator=new $hydratorClass();
        $mapper = self::mapperFactory($container, $dbAdapter, $dbTable, $dbSchema, $mapperClass, $controllerClass,
                                      $entityClass, $collectionClass, $entity, $hydrator);

        $underscoreToCamelCase = new UnderscoreToCamelCase();
        foreach ($requestedConfig as $key => $value) {
            $param = lcfirst($underscoreToCamelCase->filter($key));
            switch ($param) {
                case 'mapper-listeners':
                    foreach ($value as $listener_class) {
                        $listener = $container->get($listener_class);
                        if (method_exists($listener, 'setEventManager')) {
                            $listener->setEventManager($mapper->getEventManager());
                        }
                        self::attachListener($mapper, $listener);
                    }
                    break;
                default:
                    $mapper->getEvent()->getRequest()->getParameters()->set($param, $value);
                    break;
            }
        }

        return $mapper;

    }
    /**
     * @param $requestedConfig
     *
     * @return mixed
     */
    protected function extractConfigParam($requestedConfig, $key)
    {
        $value = $requestedConfig[$key];
        unset($requestedConfig[$key]);
        return $value;
    }
    /**
     * @param \MessageExchangeEventManager\Resultset\ResultsetHydrator $hydrator
     * @param \Interop\Container\ContainerInterface                    $container
     * @param \MessageExchangeEventManager\Event\Event                 $event
     *
     */
    protected function setHydrator($hydrator, ContainerInterface $container, Event $event)
    {
        if (!is_null($hydrator)) {
            $event->getRequest()->getParameters()->set('hydrator', $hydrator);
        } else {
            $event->getRequest()->getParameters()->set('hydrator',
                                                       $container->get('MessageExchangeEventManager\Resultset\ResultsetHydrator'))
            ;
        }
    }

    /**
     * @param \MessageExchangeEventManager\Resultset\Resultset $resultset
     * @param \Interop\Container\ContainerInterface            $container
     * @param \MessageExchangeEventManager\Event\Event         $event
     *
     */
    protected function setResultset($resultset, ContainerInterface $container, Event $event)
    {
        if (!is_null($resultset)) {
            $event->getRequest()->getParameters()->set('resultset', $resultset);
        } else {
            $event->getRequest()->getParameters()->set('resultset',
                                                       $container->get('MessageExchangeEventManager\Resultset\Resultset'))
            ;
        }
    }

    /**
     * @param \Interop\Container\ContainerInterface $container
     *
     * @return \MessageExchangeEventManager\Event\Event
     */
    protected function getEvent(ContainerInterface $container)
    {
        $request = new Request();
        $response = new Response();
        $event = new Event();
        $event->setRequest($request);
        $event->setResponse($response);

        return $event;
    }

    /**
     * @param $requestedName
     * @param $sqlActuatorMapperConfig
     *
     * @return array
     */
    protected function getRequestedConfig($requestedName, $sqlActuatorMapperConfig)
    {
        $requestedConfig = $sqlActuatorMapperConfig[$requestedName];
        if (empty($requestedConfig)) {
            throw new ServiceNotCreatedException('configuration missed for ' . $requestedName, 500);
        }
        $defaultConfig = $sqlActuatorMapperConfig['mapper_default'];
        $merged = ArrayUtils::merge($defaultConfig, $requestedConfig);

        return $merged;
    }


    /**
     * @param \ApigilityTools\Mapper\Mapper                                                                     $mapper
     * @param                                                                                                   $listener
     * @param null                                                                                              $priority
     */
    private function attachListener(Mapper $mapper, $listener, $priority = null)
    {

        $eventManager = $mapper->getEventManager();
        if ($priority) {
            $listener->attach($eventManager, $priority);
        } else {
            $listener->attach($eventManager);
        }

    }
}
