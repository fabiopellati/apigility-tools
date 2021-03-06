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

namespace ApigilityTools\SqlActuator\Listener\Feature;

use Interop\Container\ContainerInterface;
use Zend\Cache\StorageFactory;
use Zend\ServiceManager\Factory\FactoryInterface;

class SoftDeleteListenerFactory
    implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string             $requestedName
     * @param  null|array         $options
     *
     * @return object
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $object = new SoftDeleteListener();
        $config = $container->get('Config');
        if (isset($config['caches']['apigility_tools_model_cache'])) {
            $cache_config = $config['caches']['apigility_tools_model_cache'];
            $cache = StorageFactory::factory($cache_config);
            $object->setCache($cache);
        }

        return $object;
    }

}
