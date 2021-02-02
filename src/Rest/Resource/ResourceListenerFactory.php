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

namespace ApigilityTools\Rest\Resource;

use Interop\Container\ContainerInterface;
use Laminas\Filter\Word\UnderscoreToCamelCase;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ResourceListenerFactory
    implements FactoryInterface
{

    /**
     * @param \Interop\Container\ContainerInterface $container
     * @param string                                $requestedName
     * @param array|null                            $options
     *
     * @return \ApigilityTools\Rest\Resource\ResourceListener|object
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {

        $config = $container->get('Config');
        $sqlActuatorMapperConfig = $config['apigility-tools']['actuator-mapper'];
        $requestedConfig = $sqlActuatorMapperConfig[$requestedName];
        if (empty($requestedConfig) || empty($requestedConfig['mapper_class'])) {
            throw new ServiceNotCreatedException('actuator-mapper configuration missed for mapper_class in ' .
                                                 $requestedName, 500);
        }
        $mapperClass = $requestedConfig['mapper_class'];
        $mapper = $container->get($mapperClass);
        $resource = new ResourceListener($mapper);
        $underscoreToCamelCase = new UnderscoreToCamelCase();
        foreach ($requestedConfig as $key => $value) {
            $param = lcfirst($underscoreToCamelCase->filter($key));
            switch ($param) {
                case 'halListeners':
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
