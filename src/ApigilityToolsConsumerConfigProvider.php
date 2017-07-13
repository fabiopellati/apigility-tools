<?php
/**
 * @link      http://github.com/zendframework/zend-form for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ApigilityTools;

use Zend\Http\Client;
use Zend\Http\Request;

class ApigilityToolsConsumerConfigProvider
{

    /**
     * Return application-level dependency configuration.
     *
     * @return array
     */
    public static function getApigilityServiceRoute($url)
    {
        $request = new Request();
        $request->setUri($url);
        $request->getHeaders()->addHeaders([
                                               'content-type' => 'application/json',
                                               'accept'       => 'application/json',
                                           ])
        ;;

        $client = new Client();
        $response = $client->send($request);
        if ($response->getStatusCode() != 200) {
            throw new \RuntimeException($url . 'inaccessibile');
        }
        $content = json_decode($response->getBody(), true);
        $child_routes = [];
        foreach ($content['services'] as $service) {
            $child_routes[$service['name']] = [
                'type'    => 'Segment',
                'options' => [
                    'route' => $service['route'],
                ],
            ];
        }

        return $child_routes;

    }

}
