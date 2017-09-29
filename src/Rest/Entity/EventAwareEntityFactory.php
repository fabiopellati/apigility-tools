<?php

namespace ApigilityTools\Rest\Entity;

use Interop\Container\ContainerInterface;
use Zend\Filter\Word\UnderscoreToCamelCase;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Stdlib\ArrayUtils;

class EventAwareEntityFactory
    implements FactoryInterface
{


    /**
     * @param \Interop\Container\ContainerInterface $container
     * @param string                                $requestedName
     * @param array|null                            $options
     *
     * @return \ApigilityTools\Rest\Entity\EventAwareEntity
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entity = new $requestedName();
        $config = $container->get('Config');
        $sqlActuatorMapperConfig = $config['apigility-tools']['actuator-mapper'];
        $halMetadataMap = $config['zf-hal']['metadata_map'];
        $entityIdentifierName = $halMetadataMap[$requestedName]['entity_identifier_name'];
        $entity->getEvent()->getRequest()->getParameters()->set('identifierName', $entityIdentifierName);

        $requestedConfig = $this->getRequestedConfig($requestedName, $sqlActuatorMapperConfig);
        if (isset($requestedConfig)) {

            $underscoreToCamelCase = new UnderscoreToCamelCase();
            foreach ($requestedConfig as $key => $value) {

                $param = lcfirst($underscoreToCamelCase->filter($key));
                switch ($param) {
                    case 'entity-listeners':
                        foreach ($value as $listener_class) {
                            $listener = $container->get($listener_class);
                            if (method_exists($listener, 'setEventManager')) {
                                $listener->setEventManager($entity->getEventManager());
                            }
                            self::attachListener($entity, $listener);
                        }
                        break;
                    default:
                        $entity->getEvent()->getRequest()->getParameters()->set($param, $value);
                        break;
                }
            }
        }

        return $entity;
    }

    /**
     * @param $requestedName
     * @param $sqlActuatorMapperConfig
     *
     * @return array|null
     */
    protected function getRequestedConfig($requestedName, $sqlActuatorMapperConfig)
    {
        if (isset($sqlActuatorMapperConfig[$requestedName])) {

            $requestedConfig = $sqlActuatorMapperConfig[$requestedName];
            if (empty($requestedConfig)) {
                throw new ServiceNotCreatedException('configuration missed for ' . $requestedName, 500);
            }
            $defaultConfig = $sqlActuatorMapperConfig['entity_default'];
            if (is_array($defaultConfig)) {
                $merged = ArrayUtils::merge($defaultConfig, $requestedConfig);

                return $merged;
            }

            return $requestedConfig;
        } else {
            return null;
        }

    }

    /**
     * @param \ApigilityTools\Rest\Entity\EventAwareEntity     $entity
     * @param                                                  $listener
     * @param null                                             $priority
     */
    protected function attachListener(EventAwareEntity $entity, $listener, $priority = null)
    {

        $eventManager = $entity->getEventManager();
        if ($priority) {
            $listener->attach($eventManager, $priority);
        } else {
            $listener->attach($eventManager);
        }

    }
}
