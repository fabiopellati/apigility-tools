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
            'default' => [
                'composite_key' => false,
                'mapper_class'  => 'ApigilityTools\\Mapper\\SqlActuatorMapper',

                'listeners' => [
                    'ApigilityTools\Listener\Sql\SqlFetchListener',
                    'ApigilityTools\Listener\Sql\SqlFetchAllListener',
                    'ApigilityTools\Listener\Sql\SqlCreateListener',
                    'ApigilityTools\Listener\Sql\SqlUpdateListener',
                    'ApigilityTools\Listener\Sql\SqlDeleteListener',
                    'ApigilityTools\Listener\Query\ConstraintWhereListener',
                    'ApigilityTools\Listener\Query\SelectQueryListener',
                    'ApigilityTools\Listener\Query\UpdateQueryListener',
                    'ApigilityTools\Listener\Query\DeleteQueryListener',
                    'ApigilityTools\Listener\Query\InsertQueryListener',
                    'ApigilityTools\Listener\Query\RunQueryListener',
                    'ApigilityTools\Listener\Sql\SqlPaginatorListener',
                    'ApigilityTools\Listener\HydratorDbResultListener',
                    'ApigilityTools\\Listener\\Query\\WhereIdListener',
                    'ApigilityTools\\Listener\\HydratorPreLimitedDbResultsetCollectionListener',
                    'ApigilityTools\\Listener\\Query\\CountAffectedQueryListener',
                ],

            ],
        ],
    ],

    'service_manager' => [
        'factories' => [
            /**
             * actuator sql
             */
            'ApigilityTools\\Listener\\Sql\\SqlFetchListener'                           => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Listener\\Sql\\SqlFetchAllListener'                        => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Listener\\Sql\\SqlCreateListener'                          => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Listener\\Sql\\SqlUpdateListener'                          => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Listener\\Sql\\SqlDeleteListener'                          => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Listener\\Sql\\SqlPatchListener'                           => 'Zend\\ServiceManager\\Factory\\InvokableFactory',

            /**
             * utility sql
             */
            'ApigilityTools\\Listener\\Sql\\SqlOrderableListener'                       => 'ApigilityTools\\Listener\\Sql\\SqlOrderableListenerFactory',
            'ApigilityTools\\Listener\\Sql\\SqlSearchableListener'                      => 'ApigilityTools\\Listener\\Sql\\SqlSearchableListenerFactory',
            'ApigilityTools\\Listener\\Sql\\SqlFilterTextListener'                      => 'ApigilityTools\\Listener\\Sql\\SqlFilterTextListenerFactory',
            'ApigilityTools\\Listener\\Sql\\SoftDeleteListener'                         => 'ApigilityTools\\Listener\\Sql\\SoftDeleteListenerFactory',
            'ApigilityTools\\Listener\\Sql\\SqlPaginatorListener'                       => 'Zend\\ServiceManager\\Factory\\InvokableFactory',

            /**
             * query listeners
             */
            'ApigilityTools\\Listener\\Query\\CountAffectedQueryListener'               => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Listener\\Query\\SelectQueryListener'                      => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Listener\\Query\\InsertQueryListener'                      => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Listener\\Query\\UpdateQueryListener'                      => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Listener\\Query\\DeleteQueryListener'                      => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Listener\\Query\\RunQueryListener'                         => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Listener\\Query\\ConstraintWhereListener'                  => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Listener\\Query\\WhereKeysListener'                        => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Listener\\Query\\WhereIdListener'                          => 'Zend\\ServiceManager\\Factory\\InvokableFactory',

            /**
             * hydrators
             */
            'ApigilityTools\\Listener\\HydratorResultsetListener'                       => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Listener\\HydratorDbResultListener'                        => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Listener\\HydratorDbResultsetCollectionListener'           => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Listener\\HydratorPreLimitedDbResultsetCollectionListener' => 'Zend\\ServiceManager\\Factory\\InvokableFactory',

            /**
             * Entity
             */
            'ApigilityTools\\Listener\\Entity\\CompositeKeysListenerAggregate'          => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
        ],
    ],
];
