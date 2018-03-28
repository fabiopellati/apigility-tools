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

namespace HrmsApi\V1\Rest\ServiceHasManyName;
return [
    'service_manager' => [
        'factories' => [
            __NAMESPACE__ .
            '\\ServiceHasManyNameResource'               => \ApigilityTools\Rest\Resource\ResourceListenerFactory::class,
            __NAMESPACE__ . '\\Mapper'                   => \ApigilityTools\Mapper\MapperFactory::class,
            __NAMESPACE__ . '\\ServiceHasManyNameEntity' => \ApigilityTools\Rest\Entity\EventAwareEntityFactory::class,
        ],
    ],
    'apigility-tools' => [
        'actuator-mapper' => [
            __NAMESPACE__ . '\\ServiceHasManyNameResource' => [
                'mapper_class' => __NAMESPACE__ . '\\Mapper',
            ],
            __NAMESPACE__ . '\\Mapper'                     => [
                'namespace'                          => __NAMESPACE__,
                'db_adapter'                         => 'Db\\Adapter\\Locale',
                'db_schema'                          => 'schema',
                'db_table'                           => 'service_table',
                'route_association_identifier_name'  => 'has_many_id',
                'entity_association_identifier_name' => 'id',
                'association_joins'                  => [
                    [
                        'entity_association_identifier_name' => 'service_id_in_has_table',
                        'route_association_identifier_name'  => 'service_id_in_route',
                        'db_schema'                          => 'has_schema',
                        'db_table'                           => 'service_has_associated',
                        'on'                                 => [
                            ['id', 'badge_id'],
                        ],
                        'columns'                            => ['attivo_da', 'attivo_a',],
                    ],
                ],
                'mapper-listeners'                   => [
                    'ApigilityTools\\SqlActuator\\Listener\\Query\\WhereIdListener',
                    'ApigilityTools\\SqlActuator\\Listener\\Feature\\AssociationManyListener',
                ],
            ],
            __NAMESPACE__ . '\\ServiceHasManyNameEntity'   => [
                'entity-listeners' => [
                ],
            ],
        ],
    ],
];
