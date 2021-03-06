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
return [
    'apigility-tools' => [
        'actuator-mapper' => [
            'entity_default' => [
            ],
            'mapper_default' => [
                'mapper_class'     => 'ApigilityTools\\Mapper\\Mapper',
                'mapper-listeners' => [
                    'ApigilityTools\\SqlActuator\\Listener\\SqlListener'                                     => 'ApigilityTools\\SqlActuator\\Listener\\SqlListener',
                    'ApigilityTools\\Mapper\\Listener\\PrepareMvcRequestParamsListener'                      => 'ApigilityTools\\Mapper\\Listener\\PrepareMvcRequestParamsListener',
                    'ApigilityTools\\SqlActuator\\Listener\\FetchListener'                                   => 'ApigilityTools\\SqlActuator\\Listener\\FetchListener',
                    'ApigilityTools\\SqlActuator\\Listener\\FetchAllListener'                                => 'ApigilityTools\\SqlActuator\\Listener\\FetchAllListener',
                    //                    'ApigilityTools\\SqlActuator\\Listener\\CreateListener'=>'ApigilityTools\\SqlActuator\\Listener\\CreateListener',
                    //                    'ApigilityTools\\SqlActuator\\Listener\\UpdateListener'=>'ApigilityTools\\SqlActuator\\Listener\\UpdateListener',
                    //                    'ApigilityTools\\SqlActuator\\Listener\\DeleteListener'=>'ApigilityTools\\SqlActuator\\Listener\\DeleteListener',
                    //                    'ApigilityTools\\SqlActuator\\Listener\\Feature\\PaginatorListener'                      => 'ApigilityTools\\SqlActuator\\Listener\\Feature\\PaginatorListener',
                    'ApigilityTools\\SqlActuator\\Listener\\Query\\ConstraintWhereListener'                  => 'ApigilityTools\\SqlActuator\\Listener\\Query\\ConstraintWhereListener',
                    'ApigilityTools\\SqlActuator\\Listener\\Query\\SelectQueryListener'                      => 'ApigilityTools\\SqlActuator\\Listener\\Query\\SelectQueryListener',
                    //                    'ApigilityTools\\SqlActuator\\Listener\\Query\\UpdateQueryListener'=>'ApigilityTools\\SqlActuator\\Listener\\Query\\UpdateQueryListener',
                    //                    'ApigilityTools\\SqlActuator\\Listener\\Query\\DeleteQueryListener'=>'ApigilityTools\\SqlActuator\\Listener\\Query\\DeleteQueryListener',
                    //                    'ApigilityTools\\SqlActuator\\Listener\\Query\\InsertQueryListener'=>'ApigilityTools\\SqlActuator\\Listener\\Query\\InsertQueryListener',
                    'ApigilityTools\\SqlActuator\\Listener\\Query\\RunQueryListener'                         => 'ApigilityTools\\SqlActuator\\Listener\\Query\\RunQueryListener',
                    //                    'ApigilityTools\\SqlActuator\\Listener\\Query\\WhereIdListener'=>'ApigilityTools\\SqlActuator\\Listener\\Query\\WhereIdListener',
                    'ApigilityTools\\SqlActuator\\Listener\\Query\\CountAffectedQueryListener'               => 'ApigilityTools\\SqlActuator\\Listener\\Query\\CountAffectedQueryListener',
                    'ApigilityTools\\SqlActuator\\Hydrator\\HydratorDbResultListener'                        => 'ApigilityTools\\SqlActuator\\Hydrator\\HydratorDbResultListener',
                    'ApigilityTools\\SqlActuator\\Hydrator\\HydratorPreLimitedDbResultsetCollectionListener' => 'ApigilityTools\\SqlActuator\\Hydrator\\HydratorPreLimitedDbResultsetCollectionListener',
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            'ApigilityTools\\Mapper\\Listener\\ComposedKeysListener'                   => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Mapper\\Listener\\PrepareMvcRequestParamsListener'        => 'ApigilityTools\\Mapper\\Listener\\PrepareMvcRequestParamsListenerFactory',
            /**
             * actuator sql
             */
            'ApigilityTools\\SqlActuator\\Listener\\SqlListener'                       => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\FetchListener'                     => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\FetchAllListener'                  => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\CreateListener'                    => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\UpdateListener'                   => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\DeleteListener'                   => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\PatchListener'                     => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            /**
             * utility sql
             */
            'ApigilityTools\\SqlActuator\\Listener\\Feature\\AssociationListener'      => 'ApigilityTools\\SqlActuator\\Listener\\Feature\\AssociationListenerFactory',
            'ApigilityTools\\SqlActuator\\Listener\\Feature\\AssociationManyListener'  => 'ApigilityTools\\SqlActuator\\Listener\\Feature\\AssociationManyListenerFactory',
            'ApigilityTools\\SqlActuator\\Listener\\Feature\\OrderableListener'        => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\Feature\\SearchableListener'       => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\Feature\\FilterTextListener'       => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\Feature\\SoftDeleteListener'       => 'ApigilityTools\\SqlActuator\\Listener\\Feature\\SoftDeleteListenerFactory',
            'ApigilityTools\\SqlActuator\\Listener\\Feature\\PaginatorListener'        => 'Zend\\ServiceManager\\Factory\\InvokableFactory',

            /**
             * query listeners
             */
            'ApigilityTools\\SqlActuator\\Listener\\Query\\DebugQueryListener'         => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\Query\\CountAffectedQueryListener' => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\Query\\ColumnsListener'            => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\Query\\SelectQueryListener'        => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\Query\\InsertQueryListener'        => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\Query\\UpdateQueryListener'        => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\Query\\DeleteQueryListener'        => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\Query\\RunQueryListener'                         => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\Query\\ConstraintWhereListener'                  => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\Query\\WhereKeysListener'                        => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\Query\\WhereIdListener'                          => 'Zend\\ServiceManager\\Factory\\InvokableFactory',


            /**
             * query debug
             */
            'ApigilityTools\\SqlActuator\\Listener\\Query\\InspectQueryListener'                         => 'Zend\\ServiceManager\\Factory\\InvokableFactory',


            /**
             * hydrators
             */
            'ApigilityTools\\SqlActuator\\Hydrator\\HydratorResultsetListener'                       => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Hydrator\\HydratorDbResultListener'                        => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Hydrator\\HydratorDbResultsetCollectionListener'           => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Hydrator\\HydratorPreLimitedDbResultsetCollectionListener' => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            /**
             * Entity
             */
            'ApigilityTools\\Rest\\Entity\\Listener\\CompositeKeysListenerAggregate'                 => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Rest\\Entity\\Listener\\InputFilterListenerAggregate'                   => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Rest\\Entity\\Listener\\AliasFieldsListenerAggregate'                   => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
        ],
    ],
];
