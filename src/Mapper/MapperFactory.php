<?php
/**
 *
 * apigility-tools (https://github.com/fabiopellati/apigility-tools)
 *
 * @link      https://github.com/fabiopellati/apigility-tools for the canonical source repository
 * @copyright Copyright (c) 2017 Fabio Pellati (https://github.com/fabiopellati)
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 *
 */

namespace ApigilityTools\Mapper;

use ApigilityTools\Rest\Entity\EventAwareEntity;
use Interop\Container\ContainerInterface;
use MessageExchangeEventManager\Event\Event;
use MessageExchangeEventManager\Request\Request;
use MessageExchangeEventManager\Response\Response;
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
     * l'utilizzo della factory invocandola, prevede la compilazione di adeguata configurazione
     * sotto la chiave ['apigility-tools']['actuator-mapper']
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param string                                $requestedName
     * @param array|null                            $options
     *
     * @return object
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $event = $this->getEvent($container);
        $config = $container->get('Config');
        $actuatorMapperConfig = $config['apigility-tools']['actuator-mapper'];
        $halMetadataMapConfig = $config['zf-hal']['metadata_map'];
        $requestedConfig = $this->getRequestedConfig($requestedName, $actuatorMapperConfig);
        $namespace = $this->extractConfigParam($requestedConfig, 'namespace');
        $controllerClass = $namespace . '\Controller';
        $controllerConfig = $config['zf-rest'][$controllerClass];
        $entityClass = $controllerConfig['entity_class'];
        $collectionClass = $controllerConfig['collection_class'];
        $apigilityConfig = $config['zf-rest'][$controllerClass];
        $requestParameters = $event->getRequest()->getParameters();
        $requestParameters->set('actuatorMapperConfig', $actuatorMapperConfig);
        $requestParameters->set('halMetadataMapConfig', $halMetadataMapConfig);
        $requestParameters->set('namespace', $namespace);
        $requestParameters->set('entityClass', $entityClass);
        $requestParameters->set('collectionClass', $collectionClass);
        $requestParameters->set('identifierName', $halMetadataMapConfig[$entityClass]['entity_identifier_name']);
        $requestParameters->set('apigilityConfig', $apigilityConfig);
        $mapper = $this->setMapper($requestedConfig, $event);
        $entity = $container->get($entityClass);
        if (!$entity instanceof EventAwareEntity) {
            throw new ServiceNotCreatedException('Entity must be instance of EventAwareEntity', 500);
        }
        $hydratorClass = $halMetadataMapConfig[$entityClass]['hydrator'];
        $hydrator = new $hydratorClass();
        $this->setHydrator($hydrator, $container, $event);
        $this->setResultset($entity, $container, $event);
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
                    if ($container->has($value)) {
                        $mapper->getEvent()->getRequest()->getParameters()->set($param, $container->get($value));
                    } else {
                        $mapper->getEvent()->getRequest()->getParameters()->set($param, $value);

                    }
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
     * @param $requestedConfig
     * @param $event
     *
     * @return \ApigilityTools\Mapper\Mapper
     */
    protected function setMapper($requestedConfig, $event)
    {
        $mapperClass = $this->extractConfigParam($requestedConfig, 'mapper_class');
        $mapper = new $mapperClass($event);
        if (!$mapper instanceof Mapper) {
            throw new ServiceNotCreatedException('$mapperClass must be instance of ApigilityTools\Mapper\Mapper ' .
                                                 $mapperClass . ' given',
                                                 500);
        }
        $event->getRequest()->getParameters()->set('mapper', $mapper);
        $event->getRequest()->getParameters()->set('mapperClass', $mapperClass);

        return $mapper;

    }

    /**
     * @param \ApigilityTools\Mapper\Mapper $mapper
     * @param                               $listener
     * @param null                          $priority
     */
    protected function attachListener(Mapper $mapper, $listener, $priority = null)
    {

        $eventManager = $mapper->getEventManager();
        if ($priority) {
            $listener->attach($eventManager, $priority);
        } else {
            $listener->attach($eventManager);
        }

    }
}
