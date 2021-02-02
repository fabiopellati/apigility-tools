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

namespace ApigilityTools\SqlActuator\Listener\Query;

use ApigilityTools\SqlActuator\Listener\SqlActuatorListenerInterface;
use MessageExchangeEventManager\Event\Event;
use MessageExchangeEventManager\Exception\ListenerRequirementException;
use Laminas\Db\Sql\AbstractPreparableSql;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Having;
use Laminas\Db\Sql\Join;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Where;
use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;

class CountAffectedQueryListener
    extends AbstractListenerAggregate
{

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
    public function attach(EventManagerInterface $events, $priority = 100)
    {

        $this->listeners[] = $events->attach(SqlActuatorListenerInterface::EVENT_SQL_SELECT, [$this, 'onEvent'],
                                             $priority + 100);
        $this->listeners[] = $events->attach(SqlActuatorListenerInterface::EVENT_SQL_UPDATE, [$this, 'onEvent'],
                                             $priority + 100);
        $this->listeners[] = $events->attach(SqlActuatorListenerInterface::EVENT_SQL_DELETE, [$this, 'onEvent'],
                                             $priority + 100);
    }

    /**
     * @param \MessageExchangeEventManager\Event\Event $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     * @throws \MessageExchangeEventManager\Exception\ListenerRequirementException
     */
    public function onEvent(Event $e)
    {
        $response = $e->getResponse();
        try {
            $request = $e->getRequest();
            $sql = $request->getParameters()->get('sql');
            $this->validateSql($sql);
            $query = $request->getParameters()->get('query');
            $this->validateQuery($query);
            $countAffected = $this->countAffected($sql, $query);
            if (isset($countAffected['count_affected'])) {
                $request->getParameters()->set('count_affected', $countAffected['count_affected']);
            } else {
                if (isset($countAffected['COUNT_AFFECTED'])) {
                    $request->getParameters()->set('count_affected', $countAffected['COUNT_AFFECTED']);

                }
            }

        } catch (\Exception $error) {
            $response->setContent($error);
            $e->stopPropagation();
        }

        return $response;
    }

    /**
     * @param $query
     *
     * @throws \MessageExchangeEventManager\Exception\ListenerRequirementException
     */
    protected function validateQuery($query)
    {
        if (empty($query) || !$query instanceof AbstractPreparableSql) {
            throw new ListenerRequirementException('parametro query non presente: possibile errore nella sequenza dei listener ',
                                                   500);
        }
    }

    /**
     * @param $sql
     *
     * @throws \MessageExchangeEventManager\Exception\ListenerRequirementException
     */
    protected function validateSql($sql)
    {
        if (empty($sql) || !$sql instanceof Sql) {
            throw new ListenerRequirementException('parametro Sql non presente: possibile errore nella sequenza dei listener ',
                                                   500);
        }
    }

    /**
     * @param $sql
     * @param $query
     *
     * @return mixed
     */
    protected function countAffected($sql, $query)
    {
        $select = $sql->select();
        $select->columns(['count_affected' => new Expression('count(*)')]);
        $where = $query->where;
        if ($where instanceof Where) {
            $select->where($where);
        }
        $having = $query->having;
        if ($having instanceof Having) {
            $select->having($having);
        }
        $joins = $query->joins;
        if ($joins instanceof Join) {
            foreach ($joins->getJoins() as $join) {
                /**
                 * @var $join
                 */
                $select->join($join['name'], $join['on'], [], $join['type']);

            }
        }
        $statement = $sql->prepareStatementForSqlObject($select);
        $current = $statement->execute()->current();

        return $current;
    }

}
