<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ApigilityTools;

use Zend\Mvc\MvcEvent;
use Zend\Uri\UriFactory;

class Module
{

    public function getConfig()
    {

        return include __DIR__ . '/../config/module.config.php';
    }

    public function onBootstrap(MvcEvent $event)
    {
        UriFactory::registerScheme('chrome-extension', 'Zend\Uri\Uri');
    }
}
