<?php
/**
 *
 *
 */

namespace ApigilityTools\Mapper;

use Interop\Container\ContainerInterface;
use MessageExchangeEventManager\Event\Event;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\TableIdentifier;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;

/**
 * Class DefaultTableGatewayMapper
 * espone metodi per l'interazione con la base dati con comportamenti standard
 *
 * si possono modificare i comportamenti dei metodi in override sulle classi estese
 * oppure alterare i select intercettando gli eventi
 *
 * @package ApigilityTools
 */
abstract class SqlActuatorMapperFactory
{
    public static function mapperFactory(ContainerInterface $container, Adapter $dbAdapter, $table, $schema, $mapperClass, $controllerClass, $entityClass, $collectionClass, $resultset = null, $hydrator = null)
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
        self::setMapperListeners($container, $mapper);

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
            $entityClass => $metadataMap[$entityClass], $collectionClass => $metadataMap[$collectionClass]
        ];
        $event->getRequest()->getParameters()->set('halConfig', $halConfig);
        $event->getRequest()->getParameters()->set('identifierName',
                                                   $halConfig[$entityClass]['entity_identifier_name']);
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
     * @param \MessageExchangeEventManager\Resultset\Resultset $resultset
     * @param \Interop\Container\ContainerInterface            $container
     * @param \MessageExchangeEventManager\Event\Event         $event
     *
     */
    protected function setResultset($resultset, ContainerInterface $container, Event $event)
    {

        if (!is_null($resultset)) {
            $event->getRequest()->setResultset($resultset);
        } else {

            $event->getRequest()->setResultset($container->get('MessageExchangeEventManager\Resultset\Resultset'));
        }
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
            $event->getRequest()->setResultset($hydrator);
        } else {

            $event->getRequest()->setResultset($container->get('MessageExchangeEventManager\Resultset\ResultsetHydrator'));
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
     *
     * @param $container
     * @param $mapper
     */
    private function setMapperListeners($container, $mapper)
    {
        $sqlActuatorListener = $container->get('ApigilityTools\Listener\Sql\SqlActuatorListener');
        $sqlActuatorListener->setEventManager($mapper->getEventManager());
        self::attachListener($mapper, $sqlActuatorListener);

        $constraintWhereListener = $container->get('ApigilityTools\Listener\Query\ConstraintWhereListener');
        $constraintWhereListener->setEventManager($mapper->getEventManager());
        self::attachListener($mapper, $constraintWhereListener);
        self::attachListener($mapper, $container->get('ApigilityTools\Listener\Query\SelectQueryListener'));

//        self::attachListener($mapper, $container->get('ApigilityTools\Listener\Query\UpdateQueryListener'));
//        self::attachListener($mapper, $container->get('ApigilityTools\Listener\Query\DeleteQueryListener'));
//        self::attachListener($mapper, $container->get('ApigilityTools\Listener\Query\InsertQueryListener'));
        self::attachListener($mapper, $container->get('ApigilityTools\Listener\Query\RunQueryListener'));
        self::attachListener($mapper, $container->get('ApigilityTools\Listener\SqlPaginatorListener'));

        self::attachListener($mapper, $container->get('ApigilityTools\Listener\HydratorDbResultListener'), 10000);
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
