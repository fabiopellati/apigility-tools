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

namespace ApigilityTools\Config;

use Interop\Container\ContainerInterface;
use Laminas\ApiTools\Configuration\ModuleUtils;

class ResourceFactoryFactory
{
    /**
     * @param ContainerInterface $container
     *
     * @return ResourceFactory
     */
    public function __invoke(ContainerInterface $container)
    {


        return new ResourceFactory(
            $container->get(ModuleUtils::class),
            $container->get('Laminas\\ApiTools\\Configuration\\ConfigWriter')
        );
    }
}
