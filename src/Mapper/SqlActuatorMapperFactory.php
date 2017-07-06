<?php
/**
 *
 *
 */

namespace ApigilityTools\Mapper;

use ApigilityTools\Listener\Entity\CompositeKeysListenerAggregate;
use ApigilityTools\Rest\EventAwareEntity;
use Interop\Container\ContainerInterface;
use MessageExchangeEventManager\Event\Event;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\TableIdentifier;
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
class SqlActuatorMapperFactory
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
     * @return \ApigilityTools\Mapper\SqlActuatorMapper
     */
    public static function mapperFactory(ContainerInterface $container, Adapter $dbAdapter, $table, $schema,
    $mapperClass, $controllerClass, $entityClass, $collectionClass, $resultset = null, $hydrator = null)
    {

        $event = self::getEvent($container);

        /**
         * @var $mapper \ApigilityTools\Mapper\SqlActuatorMapper
         *
         */
        $mapper = new $mapperClass($event);
        $event->getRequest()->getParameters()->set('mapper', $mapper);

        if (!$mapper instanceof SqlActuatorMapper) {
            throw new ServiceNotCreatedException('$mapperClass must be instance of ApigilityTools\Mapper\SqlActuatorMapper',
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

        $requestedConfig = $this->getRequestedConfig($requestedName, $sqlActuatorMapperConfig);

        $namespace = $requestedConfig['namespace'];
        $halMetadataMap = $config['zf-hal']['metadata_map'];
        $controllerClass = $namespace . '\Controller';
        $entityClass = $namespace . '\Entity';
        $collectionClass = $namespace . '\Collection';
        $hydratorClass = $halMetadataMap[$entityClass]['hydrator'];
        $dbAdapter = $container->get($requestedConfig['db_adapter']);
        $dbSchema = $requestedConfig['db_schema'];
        $dbTable = $requestedConfig['db_table'];
        $mapperClass = $requestedConfig['mapper_class'];

        $entity = new $entityClass();
        if (!$entity instanceof EventAwareEntity) {
            throw new ServiceNotCreatedException('Entity must be instance of EventAwareEntity', 500);
        }
        if ($requestedConfig['composite_key'] === true) {
            $entityIdentifierName = $halMetadataMap[$entityClass]['entity_identifier_name'];
            $listener = new CompositeKeysListenerAggregate($entityIdentifierName);
            $listener->attach($entity->getEventManager());
        }

        $hydrator=new $hydratorClass();
        $mapper = self::mapperFactory($container, $dbAdapter, $dbTable, $dbSchema, $mapperClass, $controllerClass,
                                      $entityClass, $collectionClass, $entity, $hydrator);
        foreach ($requestedConfig['listeners'] as $listener_class) {
            $listener = $container->get($listener_class);
            if (method_exists($listener, 'setEventManager')) {
                $listener->setEventManager($mapper->getEventManager());
            }
            self::attachListener($mapper, $listener);
        }

        return $mapper;

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
        $request = $container->get('MessageExchangeEventManager\\Request\\Request');
        $response = $container->get('MessageExchangeEventManager\\Response\\Response');
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
        $defaultConfig = $sqlActuatorMapperConfig['default'];
        $merged = ArrayUtils::merge($defaultConfig, $requestedConfig);

        return $merged;
    }


    /**
     * @param \ApigilityTools\Mapper\SqlActuatorMapper                                                          $mapper
     * @param                                                                                                   $listener
     * @param null                                                                                              $priority
     */
    private function attachListener(SqlActuatorMapper $mapper, $listener, $priority = null)
    {

        $eventManager = $mapper->getEventManager();
        if ($priority) {
            $listener->attach($eventManager, $priority);
        } else {
            $listener->attach($eventManager);
        }

    }
}
