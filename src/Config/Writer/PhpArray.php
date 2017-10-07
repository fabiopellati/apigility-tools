<?php

namespace ApigilityTools\Config\Writer;

use Zend\Stdlib\ArrayUtils;

class PhpArray
    extends \Zend\Config\Writer\PhpArray
{

    public function toFile($filename, $config, $exclusiveLock = true)
    {
        parent::toFile($filename, $config, $exclusiveLock);
        foreach ($this->splitServices($config, $filename) as $splitKey => $splitService) {
            $splittedFileName = $this->splittedFileName($filename, $splitKey);
            $parts = explode('/', $splittedFileName);
            $file = array_pop($parts);
            $dir = '';
            foreach ($parts as $part) {

                if (!is_dir($dir .= "/$part")) {
                    mkdir($dir);
                }
            }
//            print_r([__METHOD__=>__LINE__,$splittedFileName]);exit;
            parent::toFile($splittedFileName, $splitService, $exclusiveLock);
        }

    }

    protected function splittedFileName($filename, $splitKey)
    {
        $explodeFileName = explode('/', $filename);
        unset($explodeFileName[count($explodeFileName) - 1]);
        $explodeFileName[] = 'autoload_test';
        $explodeFileName[] = $splitKey . '.config.php';
        $splittedFileName = implode('/', $explodeFileName);

        return $splittedFileName;
    }

    protected function splitServices($config, $filename)
    {
        $services = [];
        foreach ($config['router']['routes'] as $routeKey => $route) {
            $services[$routeKey]['router']['routes'][$routeKey] = $route;
            $this->setZfVersioning($routeKey, $services);
            $controller = $route['options']['defaults']['controller'];
            list($entitiesRest, $collectionsRest, $serviceNamesRest, $controllersRest) =
                $this->setZfRest($config, $controller,
                                 $services, $routeKey);
            list($serviceNamesRpc, $controllersRpc) = $this->setZfRpc($config, $controller,
                                                                      $services, $routeKey);
            $controllers = ArrayUtils::merge($controllersRest, $controllersRpc);
            $this->setZfContentNegotiation($config, $controller, $services, $routeKey);
            $this->setZfHal($config, $controller, $services, $routeKey, $entitiesRest, $collectionsRest);
            $this->setControllers($config, $controller, $services, $routeKey, $controllers);
            $this->setZfMvcAuth($config, $controller, $services, $routeKey, $controllers);
            $inputFilters = $this->setZfContentValidation($config, $controller, $services, $routeKey, $controllers);
            $this->setInputFilterSpecs($config, $controller, $services, $routeKey, $inputFilters);
        }

        return $services;

    }

    /**
     * @param $routeKey
     * @param $services
     *
     * @return mixed
     */
    protected function setZfVersioning($routeKey, &$services)
    {
        $services[$routeKey]['zf-versioning']['uri'] = $routeKey;

        return $services;
    }

    /**
     * @param $controller
     * @param $sectionKey
     *
     * @return int
     */
    protected function isVersionOfController($controller, $sectionKey)
    {

        $segments = explode('\\', $controller);
        $version = $segments[1];
        if (preg_match('#V.#', $version)) {
            $segments[1] = '(V.)';
        }
        $patternController = implode('\\', $segments);
        $sectionPattern = explode('(V.)', $patternController);
        $pattern = '#' . preg_quote($sectionPattern[0]) . '(V.)' . preg_quote($sectionPattern[1]) . '#';
        $isVersionOfController = preg_match($pattern, $sectionKey);

        return $isVersionOfController;
    }

    /**
     * @param $config
     * @param $controller
     * @param $services
     * @param $routeKey
     *
     * @return array
     */
    protected function setZfRest($config, $controller, &$services, $routeKey)
    {
        $entities = [];
        $collections = [];
        $serviceNames = [];
        $controllers = [];
        foreach ($config['zf-rest'] as $sectionKey => $sectionConfig) {
            $isVersionOfController = $this->isVersionOfController($controller, $sectionKey);
            if ($isVersionOfController) {
                $entities[] = $sectionConfig['entity_class'];
                $collections[] = $sectionConfig['collection_class'];
                $serviceNames[] = $sectionConfig['service_Name'];
                $controllers[] = $sectionKey;
                $services[$routeKey]['zf-rest'][$sectionKey] = $sectionConfig;
            };
        }

        return [$entities, $collections, $serviceNames, $controllers];
    }

    /**
     * @param $config
     * @param $controller
     * @param $services
     * @param $routeKey
     *
     * @return array
     */
    protected function setZfRpc($config, $controller, &$services, $routeKey)
    {
        $serviceNames = [];
        $controllers = [];
        foreach ($config['zf-rpc'] as $sectionKey => $sectionConfig) {
            $isVersionOfController = $this->isVersionOfController($controller, $sectionKey);
            if ($isVersionOfController) {
                $serviceNames[] = $sectionConfig['service_Name'];
                $controllers[] = $sectionKey;
                $services[$routeKey]['zf-rpc'][$sectionKey] = $sectionConfig;
            };
        }

        return [$serviceNames, $controllers];
    }

    /**
     * @param $config
     * @param $controller
     * @param $services
     * @param $routeKey
     *
     */
    protected function setZfContentNegotiation($config, $controller, &$services, $routeKey)
    {
        foreach ($config['zf-content-negotiation']['controllers'] as $sectionKey => $sectionConfig) {
            $isVersionOfController = $this->isVersionOfController($controller, $sectionKey);
            if ($isVersionOfController) {
                $services[$routeKey]['zf-content-negotiation']['controllers'][$sectionKey] = $sectionConfig;
            };
        }
        foreach ($config['zf-content-negotiation']['accept_whitelist'] as $sectionKey => $sectionConfig) {
            $isVersionOfController = $this->isVersionOfController($controller, $sectionKey);
            if ($isVersionOfController) {
                $services[$routeKey]['zf-content-negotiation']['accept_whitelist'][$sectionKey] = $sectionConfig;
            };
        }
        foreach ($config['zf-content-negotiation']['content_type_whitelist'] as $sectionKey => $sectionConfig) {
            $isVersionOfController = $this->isVersionOfController($controller, $sectionKey);
            if ($isVersionOfController) {
                $services[$routeKey]['zf-content-negotiation']['content_type_whitelist'][$sectionKey] = $sectionConfig;
            };
        }

    }

    /**
     * @param $config
     * @param $controller
     * @param $services
     * @param $routeKey
     * @param $entities
     * @param $collections
     *
     */
    protected function setZfHal($config, $controller, &$services, $routeKey, $entities, $collections)
    {
        foreach ($config['zf-hal']['metadata_map'] as $sectionKey => $sectionConfig) {
            if (in_array($sectionKey, $entities) || in_array($sectionKey, $collections)) {
                $services[$routeKey]['zf-hal']['metadata_map'][$sectionKey] = $sectionConfig;
            };
        }

    }

    /**
     * @param $config
     * @param $controller
     * @param $services
     * @param $routeKey
     * @param $controllers
     *
     */
    protected function setControllers($config, $controller, &$services, $routeKey, $controllers)
    {
        foreach ($config['controllers']['factories'] as $sectionKey => $sectionConfig) {
            if (in_array($sectionKey, $controllers)) {
                $services[$routeKey]['controllers']['factories'][$sectionKey] = $sectionConfig;
            };
        }

    }

    /**
     * @param $config
     * @param $controller
     * @param $services
     * @param $routeKey
     * @param $controllers
     *
     */
    protected function setZfMvcAuth($config, $controller, &$services, $routeKey, $controllers)
    {
        foreach ($config['zf-mvc-auth']['authorization'] as $sectionKey => $sectionConfig) {
            if (in_array($sectionKey, $controllers)) {
                $services[$routeKey]['zf-mvc-auth']['authorization'][$sectionKey] = $sectionConfig;
            };
        }

    }

    /**
     * @param $config
     * @param $controller
     * @param $services
     * @param $routeKey
     * @param $controllers
     *
     * @return array
     */
    protected function setZfContentValidation($config, $controller, &$services, $routeKey, $controllers)
    {
        $inpitFilters = [];
        foreach ($config['zf-content-validation'] as $sectionKey => $sectionConfig) {
            if (in_array($sectionKey, $controllers)) {
                $inpitFilters[] = $sectionConfig['input_filter'];
                $services[$routeKey]['zf-content-validation'][$sectionKey] = $sectionConfig;
            };
        }

        return $inpitFilters;

    }

    /**
     * @param $config
     * @param $controller
     * @param $services
     * @param $routeKey
     * @param $inputFilters
     *
     */
    protected function setInputFilterSpecs($config, $controller, &$services, $routeKey, $inputFilters)
    {

        foreach ($config['input_filter_specs'] as $sectionKey => $sectionConfig) {
            if (in_array($sectionKey, $inputFilters)) {
                $services[$routeKey]['input_filter_specs'][$sectionKey] = $sectionConfig;
            };
        }

    }

}