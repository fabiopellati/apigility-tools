<?php

namespace ApigilityTools\Rest\Resource;

use Interop\Container\ContainerInterface;
use Zend\Filter\Word\UnderscoreToCamelCase;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Factory\FactoryInterface;

class ResourceListenerFactory
    implements FactoryInterface
{


    /**
     * @param \Interop\Container\ContainerInterface $container
     * @param string                                $requestedName
     * @param array|null                            $options
     *
     * @return \ApigilityTools\Rest\Resource\ResourceListener|object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {

        $config = $container->get('Config');
        $sqlActuatorMapperConfig = $config['apigility-tools']['sql-actuator-mapper'];
        $requestedConfig = $sqlActuatorMapperConfig[$requestedName];
        if (empty($requestedConfig) || empty($requestedConfig['mapper_class'])) {
            throw new ServiceNotCreatedException('sql-actuator-mapper configuration missed for mapper_class in ' .
                                                 $requestedName, 500);
        }
        $mapperClass = $requestedConfig['mapper_class'];
        $mapper = $container->get($mapperClass);
        $resource = new ResourceListener($mapper);

        $underscoreToCamelCase = new UnderscoreToCamelCase();
        foreach ($requestedConfig as $key => $value) {
            $param = lcfirst($underscoreToCamelCase->filter($key));
            switch ($param) {
                case 'hal_listeners':
                    $helpers = $container->get('ViewHelperManager');
                    /**
                     * @var $hal Hal
                     */
                    $hal = $helpers->get('Hal');
                    foreach ($value as $halListenerClass) {
                        $listener = $container->get($halListenerClass);
                        $listener->attach($hal->getEventManager());
                    }
                    break;
            }
        }

        return $resource;
    }



}
