<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ApigilityTools;

return [
    'apigility-tools' => [
        'sql-actuator-mapper' => [
            'entity_default' => [
                ],
            'mapper_default' => [
                'mapper_class'  => 'ApigilityTools\\Mapper\\Mapper',
                'mapper-listeners' => [
                    'ApigilityTools\\SqlActuator\\Listener\\Sql\\SqlFetchListener'=>'ApigilityTools\\SqlActuator\\Listener\\Sql\\SqlFetchListener',
                    'ApigilityTools\\SqlActuator\\Listener\\Sql\\SqlFetchAllListener'=>'ApigilityTools\\SqlActuator\\Listener\\Sql\\SqlFetchAllListener',
                    //                    'ApigilityTools\\SqlActuator\\Listener\\Sql\\SqlCreateListener'=>'ApigilityTools\\SqlActuator\\Listener\\Sql\\SqlCreateListener',
                    //                    'ApigilityTools\\SqlActuator\\Listener\\Sql\\SqlUpdateListener'=>'ApigilityTools\\SqlActuator\\Listener\\Sql\\SqlUpdateListener',
                    //                    'ApigilityTools\\SqlActuator\\Listener\\Sql\\SqlDeleteListener'=>'ApigilityTools\\SqlActuator\\Listener\\Sql\\SqlDeleteListener',
                    'ApigilityTools\\SqlActuator\\Listener\\Sql\\SqlPaginatorListener'=>'ApigilityTools\\SqlActuator\\Listener\\Sql\\SqlPaginatorListener',
                    'ApigilityTools\\SqlActuator\\Listener\\Query\\ConstraintWhereListener'=>'ApigilityTools\\SqlActuator\\Listener\\Query\\ConstraintWhereListener',
                    'ApigilityTools\\SqlActuator\\Listener\\Query\\SelectQueryListener'=>'ApigilityTools\\SqlActuator\\Listener\\Query\\SelectQueryListener',
                    //                    'ApigilityTools\\SqlActuator\\Listener\\Query\\UpdateQueryListener'=>'ApigilityTools\\SqlActuator\\Listener\\Query\\UpdateQueryListener',
                    //                    'ApigilityTools\\SqlActuator\\Listener\\Query\\DeleteQueryListener'=>'ApigilityTools\\SqlActuator\\Listener\\Query\\DeleteQueryListener',
                    //                    'ApigilityTools\\SqlActuator\\Listener\\Query\\InsertQueryListener'=>'ApigilityTools\\SqlActuator\\Listener\\Query\\InsertQueryListener',
                    'ApigilityTools\\SqlActuator\\Listener\\Query\\RunQueryListener'=>'ApigilityTools\\SqlActuator\\Listener\\Query\\RunQueryListener',
                    //                    'ApigilityTools\\SqlActuator\\Listener\\Query\\WhereIdListener'=>'ApigilityTools\\SqlActuator\\Listener\\Query\\WhereIdListener',
                    'ApigilityTools\\SqlActuator\\Listener\\Query\\CountAffectedQueryListener'=>'ApigilityTools\\SqlActuator\\Listener\\Query\\CountAffectedQueryListener',
                    'ApigilityTools\\SqlActuator\\Listener\\HydratorDbResultListener'=>'ApigilityTools\\SqlActuator\\Listener\\HydratorDbResultListener',
                    'ApigilityTools\\SqlActuator\\Listener\\HydratorPreLimitedDbResultsetCollectionListener'=>'ApigilityTools\\SqlActuator\\Listener\\HydratorPreLimitedDbResultsetCollectionListener',
                ],
            ],
        ],
    ],

    'service_manager' => [
        'factories' => [
            'ApigilityTools\\Mapper\\Listener\\ComposedKeysListener'       => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            /**
             * actuator sql
             */
            'ApigilityTools\\SqlActuator\\Listener\\Sql\\SqlFetchListener'             => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\Sql\\SqlFetchAllListener' => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\Sql\\SqlCreateListener'      => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\Sql\\SqlUpdateListener'      => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\Sql\\SqlDeleteListener'      => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\Sql\\SqlPatchListener'       => 'Zend\\ServiceManager\\Factory\\InvokableFactory',

            /**
             * utility sql
             */
            'ApigilityTools\\SqlActuator\\Listener\\Sql\\SqlAssociationListener' => 'ApigilityTools\\SqlActuator\\Listener\\Sql\\SqlAssociationListenerFactory',

            'ApigilityTools\\SqlActuator\\Listener\\Sql\\SqlOrderableListener'  => 'ApigilityTools\\SqlActuator\\Listener\\Sql\\SqlOrderableListenerFactory',
            'ApigilityTools\\SqlActuator\\Listener\\Sql\\SqlSearchableListener' => 'ApigilityTools\\SqlActuator\\Listener\\Sql\\SqlSearchableListenerFactory',
            'ApigilityTools\\SqlActuator\\Listener\\Sql\\SqlFilterTextListener'        => 'ApigilityTools\\SqlActuator\\Listener\\Sql\\SqlFilterTextListenerFactory',
            'ApigilityTools\\SqlActuator\\Listener\\Sql\\SoftDeleteListener'           => 'ApigilityTools\\SqlActuator\\Listener\\Sql\\SoftDeleteListenerFactory',
            'ApigilityTools\\SqlActuator\\Listener\\Sql\\SqlPaginatorListener'         => 'Zend\\ServiceManager\\Factory\\InvokableFactory',

            /**
             * query listeners
             */
            'ApigilityTools\\SqlActuator\\Listener\\Query\\CountAffectedQueryListener' => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\Query\\SelectQueryListener'        => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\Query\\InsertQueryListener'        => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\Query\\UpdateQueryListener'        => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\Query\\DeleteQueryListener'        => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\Query\\RunQueryListener'           => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\Query\\ConstraintWhereListener'    => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\Query\\WhereKeysListener'       => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\Query\\WhereIdListener'         => 'Zend\\ServiceManager\\Factory\\InvokableFactory',

            /**
             * hydrators
             */
            'ApigilityTools\\SqlActuator\\Listener\\HydratorResultsetListener'      => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\HydratorDbResultListener'              => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\HydratorDbResultsetCollectionListener' => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\SqlActuator\\Listener\\HydratorPreLimitedDbResultsetCollectionListener' => 'Zend\\ServiceManager\\Factory\\InvokableFactory',

            /**
             * Entity
             */
            'ApigilityTools\\Rest\\Entity\\Listener\\CompositeKeysListenerAggregate'                 => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Rest\\Entity\\Listener\\InputFilterListenerAggregate'                 => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
        ],
    ],
];
