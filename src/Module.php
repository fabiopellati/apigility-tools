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
