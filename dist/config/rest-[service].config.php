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
//namespace [Api]\V1\Rest\[Service];
return [
    'service_manager' => [
        'factories' => [
            __NAMESPACE__ . '\\Resource' => \ApigilityTools\Rest\Resource\ResourceListenerFactory::class,
            __NAMESPACE__ . '\\Mapper'   => \ApigilityTools\Mapper\MapperFactory::class,
            __NAMESPACE__ . '\\Entity'   => \ApigilityTools\Rest\Entity\EventAwareEntityFactory::class,
        ],
    ],
    'apigility-tools' => [
        'actuator-mapper' => [
            __NAMESPACE__ . '\\Resource' => [
                'mapper_class' => __NAMESPACE__ . '\\Mapper',
            ],
            __NAMESPACE__ . '\\Mapper'   => [
                'namespace'        => __NAMESPACE__,
                'db_adapter'       => 'db_adapter',
                'db_schema'        => 'db_schema',
                'db_table'         => 'service',

                'mapper-listeners' => [
                    'ApigilityTools\\SqlActuator\\Listener\\Query\\WhereIdListener',
                ],
            ],
            __NAMESPACE__ . '\\Entity'   => [
                'entity-listeners' => [

                ],
            ],
        ],
    ],
];
