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

/**
 * Created by PhpStorm.
 * User: fabio
 * Date: 01/02/18
 * Time: 15.17
 */

namespace ApigilityTools\Hal;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class HalLinkListenerFactory
    implements FactoryInterface
{

    /**
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
        /**
         * @var $application \Laminas\Mvc\Application
         */
        $application = $container->get('Application');
        $routeMatch = $application->getMvcEvent()->getRouteMatch();
        /**
         * @var $object \Laminas\EventManager\AbstractListenerAggregate
         */
        $object = new $requestedName($routeMatch);

        return $object;
    }
}
