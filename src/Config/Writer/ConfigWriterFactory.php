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

namespace ApigilityTools\Config\Writer;

use Interop\Container\ContainerInterface;

class ConfigWriterFactory
{
    /**
     * Create and return a PhpArray config writer.
     *
     * @param ContainerInterface $container
     *
     * @return PhpArray
     */
    public function __invoke(ContainerInterface $container)
    {
        $writer = new PhpArray();
        if ($this->discoverConfigFlag($container, 'enable_short_array')) {
            $writer->setUseBracketArraySyntax(true);
        }
        if ($this->discoverConfigFlag($container, 'class_name_scalars')) {
            $writer->setUseClassNameScalars(true);
        }

        return $writer;
    }

    /**
     * Discover the $key flag from configuration, if present.
     *
     * @param ContainerInterface $container
     * @param string             $key
     *
     * @return bool
     */
    private function discoverConfigFlag(ContainerInterface $container, $key)
    {
        if (!$container->has('config')) {
            return false;
        }
        $config = $container->get('config');
        if (!isset($config['zf-configuration'][$key])) {
            return false;
        }

        return (bool)$config['zf-configuration'][$key];
    }
}
