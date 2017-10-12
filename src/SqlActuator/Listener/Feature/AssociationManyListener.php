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

namespace ApigilityTools\SqlActuator\Listener\Feature;

use ApigilityTools\Exception\RuntimeException;
use ApigilityTools\SqlActuator\Listener\SqlActuatorListenerInterface;
use MessageExchangeEventManager\Event\Event;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\Where;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Stdlib\ArrayUtils;

class AssociationManyListener
    extends AbstractListenerAggregate
{
    /**
     * @var array
     */
    protected $params;

    /**
     * @var array
     */
    protected $affected;

    /**
     *
     * @param array $params
     *
     * @internal param array $affected
     */
    function __construct(array $params)
    {
        $this->params = $params;
    }

    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     * @param int                   $priority
     *
     * @return void
     */
    public function attach(EventManagerInterface $events, $priority = 50)
    {
        $this->listeners[] =
            $events->attach(SqlActuatorListenerInterface::EVENT_PRE_SQL_SELECT, [$this, 'onEvent'], $priority);
    }

    /**
     *
     * @param \MessageExchangeEventManager\Event\Event $e
     *
     * @return \MessageExchangeEventManager\Response\Response
     * @throws \ApigilityTools\Exception\RuntimeException
     * @internal  \ChainEvent\Request\RequestInterface
     */
    public function onEvent(Event $e)
    {
        $params = $this->params;
        $request = $e->getRequest();
        $response = $e->getResponse();
        $associationJoin = $request->getParameters()->get('associationJoins');
        if (empty($associationJoin) || count($associationJoin) === 0) {
            throw new RuntimeException('misconfigured: association_joins missed', 500);
        }
        try {
            /**
             * @var \Zend\Db\Sql\Select $query
             */
            $query = $request->getParameters()->get('query');
            foreach ($associationJoin as $joinConfiguration) {
                $routeAssociationIdentifierName = $joinConfiguration['route_association_identifier_name'];
                $valueAssociationIdentifier = $params[$routeAssociationIdentifierName];
//                if (empty($params[$routeAssociationIdentifierName])) {
//                    throw new RuntimeException('Entity not fount', 404);
//                }
                $this->addJoin($query, $joinConfiguration);
                if (!empty($valueAssociationIdentifier)) {
                    $this->addWhere($query, $joinConfiguration, $valueAssociationIdentifier);
                }
            }

        } catch (\Exception $error) {
            $response->setContent($error);
            $e->stopPropagation();
        }

        return $response;
    }

    /**
     * @param        $joinConfiguration
     * @param Select $query
     *
     * @return array
     */
    protected function addJoin($query, $joinConfiguration)
    {
        /**
         * @var TableIdentifier $tableIdentifier
         */
        $tableIdentifier = new TableIdentifier($joinConfiguration['db_table'], $joinConfiguration['db_schema']);
        $entityAssociationIdentifierName = $joinConfiguration['entity_association_identifier_name'];
        if (!empty($entityAssociationIdentifierName)) {
            $group[] = $entityAssociationIdentifierName;
        }
        $on = '';
        $and = ' ';
        foreach ($joinConfiguration['on'] as $onConfig) {
            $group[] = $onConfig[1];
            $left = 'has.' . $onConfig[1];
            $right = $onConfig[0];
            $on .= "$and $right = $left";
            $and = 'AND';
        }
        $columns = (!empty($joinConfiguration['columns']) && count($joinConfiguration['columns']) > 0)
            ? $joinConfiguration['columns'] : [];
        $select = new Select($tableIdentifier);
        $group = ArrayUtils::merge($group, $columns);
        $select->columns($group);
        $select->group($group);
        $query->join(['has' => $select], $on, $columns, Select::JOIN_INNER);

    }

    /**
     * @param $query
     * @param $joinConfiguration
     */
    protected function addWhere($query, $joinConfiguration, $valueAssociationIdentifier)
    {
        /**
         * @var TableIdentifier $tableIdentifier
         */
        $tableIdentifier = new TableIdentifier($joinConfiguration['db_table'], $joinConfiguration['db_schema']);
        $query->where(function (Where $where) use ($joinConfiguration, $tableIdentifier, $valueAssociationIdentifier) {
            $entityAssociationIdentifierName = $joinConfiguration['entity_association_identifier_name'];
            $left = 'has.' . $entityAssociationIdentifierName;
            $right = $valueAssociationIdentifier;
            $nest = $where->NEST;
            $nest->equalTo($left, $right);
            $nest->and;
        });
    }
}