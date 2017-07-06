<?php

namespace ServiceExample;


use ApigilityTools\Rest\Resource\DefaultResourceListener;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class ResourceFactory implements FactoryInterface
{


    /**
     *
     * @param            $container
     * @param            $requestedName
     * @param array|null $options
     *
     * @return DefaultResourceListener
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {

        $mapper = $container->get(__NAMESPACE__ . '\\Mapper');
        $resource = new DefaultResourceListener($mapper);
        return $resource;
    }


}
