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

use Zend\Stdlib\ArrayUtils;
use ZF\Configuration\ConfigResource;

class ResourceFactory
    extends \ZF\Configuration\ResourceFactory
{

    public function factory($moduleName)
    {

        $moduleName = $this->normalizeModuleName($moduleName);
        if (isset($this->resources[$moduleName])) {
            return $this->resources[$moduleName];
        }
        $moduleConfigPath = $this->modules->getModuleConfigPath($moduleName);
        $config = include $moduleConfigPath;
        $config = $this->mergeAutoloadConfig($config, $moduleConfigPath);
//        $this->writer->toFileUnsplitted($moduleConfigPath,$config, false);
//        $config = include $moduleConfigPath;
        $this->resources[$moduleName] = new ConfigResource($config, $moduleConfigPath, $this->writer);

        return $this->resources[$moduleName];

    }

    protected function mergeAutoloadConfig($config, $moduleConfigPath)
    {
        if (is_dir(dirname($moduleConfigPath) . '/autoload')) {
            $Directory = new \RecursiveDirectoryIterator(dirname($moduleConfigPath) . '/autoload');
            $Iterator = new \RecursiveIteratorIterator($Directory);
            $Regex = new \RegexIterator($Iterator, '#^.+\/autoload/.+(rest|rpc|api).+config\.php$#i',
                                        \RecursiveRegexIterator::GET_MATCH);
            $autoloadConfig = [];
            foreach ($Regex as $file) {
                $fileConfig = include $file[0];
                $autoloadConfig = ArrayUtils::merge($autoloadConfig, $fileConfig, true);
            }
            $config = ArrayUtils::merge($config, $autoloadConfig, true);

        }

        return $config;

    }
}
