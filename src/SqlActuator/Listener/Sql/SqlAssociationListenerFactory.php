<?php
/**
 * Created by PhpStorm.
 * User: fabio
 * Date: 22/02/17
 * Time: 17.44
 */

namespace ApigilityTools\SqlActuator\Listener\Sql;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\FactoryInterface;

class SqlAssociationListenerFactory
    implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     *
     * @return object
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     *     creating a service.
     * @throws ContainerException if any other error occurs
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /**
         * @var \Zend\Mvc\MvcEvent $mvcEvent
         */
        $mvcEvent=$container->get('Application')->getMvcEvent();
        $params = $mvcEvent->getRouteMatch()->getParams();

        $object = new SqlAssociationListener($params);
        return $object;
    }

}