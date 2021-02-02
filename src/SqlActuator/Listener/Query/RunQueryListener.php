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

use ApigilityTools\Exception\InvalidParamException;
use ApigilityTools\SqlActuator\Listener\SqlActuatorListenerInterface;
use MessageExchangeEventManager\Event\Event;
use MessageExchangeEventManager\Exception\ListenerRequirementException;
use MessageExchangeEventManager\Request\Request;
use Laminas\Db\Sql\AbstractPreparableSql;
use Laminas\Db\Sql\Sql;
use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;

class RunQueryListener
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

        $this->listeners[] =
            $events->attach(SqlActuatorListenerInterface::EVENT_SQL_UPDATE, [$this, 'onEventConstraint'],
                            $priority + 100);
        $this->listeners[] =
            $events->attach(SqlActuatorListenerInterface::EVENT_SQL_DELETE, [$this, 'onEventConstraint'],
                            $priority + 100);
        $this->listeners[] =
            $events->attach(SqlActuatorListenerInterface::EVENT_SQL_PATCH, [$this, 'onEventConstraint'],
                            $priority + 100);
        $this->listeners[] =
            $events->attach(SqlActuatorListenerInterface::EVENT_SQL_SELECT, [$this, 'onSelect'], $priority);
        $this->listeners[] =
            $events->attach(SqlActuatorListenerInterface::EVENT_SQL_INSERT, [$this, 'onInsert'], $priority);
        $this->listeners[] =
            $events->attach(SqlActuatorListenerInterface::EVENT_SQL_UPDATE, [$this, 'onUpdate'], $priority);
        $this->listeners[] =
            $events->attach(SqlActuatorListenerInterface::EVENT_SQL_DELETE, [$this, 'onDelete'], $priority);
    }

    /**
     * @param \MessageExchangeEventManager\Event\Event $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     */
    public function onSelect(Event $e)
    {
        $response = $e->getResponse();
        try {
            $request = $e->getRequest();
            $sql = $request->getParameters()->get('sql');
            $this->validateSql($sql);
            /**
             * @var \Laminas\Db\Sql\Select $query
             */
            $query = $request->getParameters()->get('query');
            $this->validateQuery($query);
            //print_r([__METHOD__=>$query->getSqlString()]);            exit;
            $result = $sql->prepareStatementForSqlObject($query)->execute();
            $response->setContent($result);
        } catch (\Exception $error) {
            $response->setContent($error);
            $e->stopPropagation();
        }

        return $response;
    }

    /**
     * @param \MessageExchangeEventManager\Event\Event $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     */
    public function onInsert(Event $e)
    {
        $response = $e->getResponse();
        try {
            $request = $e->getRequest();
            $table = $request->getParameters()->get('table');
            $sql = $request->getParameters()->get('sql');
            $this->validateSql($sql);
            $query = $request->getParameters()->get('query');
            $this->validateQuery($query);
            $data = $request->getParameters()->get('data');
            if (empty($data) || !is_array($data)) {
                throw new InvalidParamException('parametro data non valido', 500);
            }
            $query->values($data);
            $result = $sql->prepareStatementForSqlObject($query)->execute();
            if ($result->count() > 0) {
                $lastId = $result->getGeneratedValue();
                $response->setContent($lastId);
            } else {
                throw new \Exception('risorsa non creata', 422);
            }
        } catch (\Exception $error) {
            $response->setContent($error);
            $e->stopPropagation();
        }

        return $response;
    }

    /**
     * @param \MessageExchangeEventManager\Event\Event $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     * @internal $sql Sql
     */
    public function onUpdate(Event $e)
    {
        $response = $e->getResponse();
        try {
            $request = $e->getRequest();
            $sql = $request->getParameters()->get('sql');
            $this->validateSql($sql);
            $query = $request->getParameters()->get('query');
            $this->validateQuery($query);
            $data = $request->getParameters()->get('data');
            if (empty($data) || !is_array($data)) {
                throw new InvalidParamException('parametro data non valido', 500);
            }
            $this->checkAffectedRow($request);
            $query->set($data);
            $statement = $sql->prepareStatementForSqlObject($query);
            $result = $statement->execute();
            $response->setContent($result->getAffectedRows());
        } catch (\Exception $error) {
            $response->setContent($error);
            $e->stopPropagation();
        }

        return $response;

    }

    public function onDelete(Event $e)
    {
        $response = $e->getResponse();
        try {
            $request = $e->getRequest();
            $sql = $request->getParameters()->get('sql');
            $this->validateSql($sql);
            $query = $request->getParameters()->get('query');
            $this->validateQuery($query);
            $this->checkAffectedRow($request);
            $statement = $sql->prepareStatementForSqlObject($query);
            $result = $statement->execute();
            $response->setContent($result->getAffectedRows() > 0);
        } catch (\Exception $error) {
            $response->setContent($error);
            $e->stopPropagation();
        }

        return $response;

    }

    /**
     * è un validatore di sicurezza:
     * verifica che sia stato iniettato un listener che si proclama aggiungendo un parametro hasCostraintWhere
     * che ha come responsabilità quella di accodare un adeguato where di filtraggio.
     * la logica è quella di minimizzare il rischio di eseguire query di modifica senza filtri che possano modificare
     * tutte le righe della collection in oggetto
     *
     * @param \MessageExchangeEventManager\Event\Event $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     */
    public function onEventConstraint(Event $e)
    {
        $request = $e->getRequest();
        $response = $e->getResponse();
        try {
            $hasConstraint = $request->getParameters()->get('hasConstraintWhere');
            if (empty($hasConstraint) || !$hasConstraint) {
                throw new ListenerRequirementException('per eseguire modifiche sui dati è necessario aver prima attaccato un adeguato listener Constraint',
                                                       500);
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
     * @param \MessageExchangeEventManager\Request\Request $request
     *
     * @throws \Exception
     * @internal param $sql
     * @internal param $query
     */
    protected function checkAffectedRow(Request $request)
    {
        $countAffected = $this->getCountAffectedParam($request);
        if ((int)$countAffected > 1) {
            throw  new \Exception('l\operazione di modifica agirebbe su un numero di record superiore a 1', 500);
        }
    }

    /**
     * @param \MessageExchangeEventManager\Request\Request $request
     *
     * @return mixed
     * @throws \MessageExchangeEventManager\Exception\ListenerRequirementException
     */
    protected function getCountAffectedParam(Request $request)
    {
        $countAffected = $request->getParameters()->get('count_affected');
        if (empty($countAffected) && !$countAffected === 0) {
            throw new ListenerRequirementException('parametro count_affected non presente: possibile errore nella sequenza dei listener ',
                                                   500);
        }

        return $countAffected;

    }

}
