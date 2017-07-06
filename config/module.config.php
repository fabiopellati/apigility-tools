<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ApigilityTools;

return [
    'service_manager' => [
        'factories' => [
            'ApigilityTools\\Listener\\Sql\\SqlOrderableListener' => 'ApigilityTools\\Listener\\Sql\\SqlOrderableListenerFactory',
            'ApigilityTools\\Listener\\Sql\\SqlSearchableListener' => 'ApigilityTools\\Listener\\Sql\\SqlSearchableListenerFactory',
            'ApigilityTools\\Listener\\Sql\\SqlFilterTextListener' => 'ApigilityTools\\Listener\\Sql\\SqlFilterTextListenerFactory',
            'ApigilityTools\\Listener\\Sql\\SoftDeleteListener' => 'ApigilityTools\\Listener\\Sql\\SoftDeleteListenerFactory',
            'ApigilityTools\\Listener\\Sql\\SqlPaginatorListener' => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Listener\\Sql\\SqlActuatorListener' => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Listener\\Entity\\CompositeKeysListenerAggregate' => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Listener\\Query\\CountAffectedQueryListener' => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Listener\\Query\\SelectQueryListener' => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Listener\\Query\\InsertQueryListener' => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Listener\\Query\\UpdateQueryListener' => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Listener\\Query\\DeleteQueryListener' => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Listener\\Query\\RunQueryListener' => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Listener\\Query\\ConstraintWhereListener' => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Listener\\Query\\WhereKeysListener' => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Listener\\Query\\WhereIdListener' => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Listener\\HydratorResultsetListener' => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Listener\\HydratorDbResultListener' => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Listener\\HydratorDbResultsetCollectionListener' => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
            'ApigilityTools\\Listener\\HydratorPreLimitedDbResultsetCollectionListener' => 'Zend\\ServiceManager\\Factory\\InvokableFactory',
        ]
    ],
];
