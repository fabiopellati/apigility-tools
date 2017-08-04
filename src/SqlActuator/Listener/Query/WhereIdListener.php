<?php
/**
 * lo scopo di questo listener è quello di disaccoppiare la logica di filtraggio dell'id
 * per SELECT, UPDATE, DELETE
 *
 * per consentire di manipolare l'id filtrato prima dell'esecuzione della query nel caso ad esempio delle chiavi
 * composite
 *
 *
 */

namespace ApigilityTools\SqlActuator\Listener\Query;

use ApigilityTools\Exception\InvalidParamException;
use ApigilityTools\SqlActuator\Listener\SqlActuatorListenerInterface;
use MessageExchangeEventManager\Exception\ListenerRequirementException;
use Zend\Db\Sql\AbstractPreparableSql;
use Zend\Db\Sql\Predicate\Operator;
use Zend\Db\Sql\Predicate\Predicate;
use Zend\Db\Sql\Where;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;

class WhereIdListener
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

        $this->listeners[] = $events->attach(SqlActuatorListenerInterface::EVENT_PRE_SQL_DELETE, [$this, 'onDelete'], $priority);
        $this->listeners[] = $events->attach(SqlActuatorListenerInterface::EVENT_PRE_SQL_SELECT, [$this, 'onSelect'], $priority);
        $this->listeners[] = $events->attach(SqlActuatorListenerInterface::EVENT_PRE_SQL_UPDATE, [$this, 'onUpdate'], $priority);
    }

    /**
     *
     * @param \Zend\EventManager\Event $e
     */
    public function onSelect(Event $e)
    {
        $request = $e->getRequest();
        $id = $request->getParameters()->get('id');
        if (empty($id)) {
            return $e->getResponse();
        }

        return $this->onEvent($e);
    }

    /**
     *
     * @param \Zend\EventManager\Event $e
     */
    public function onDelete(Event $e)
    {
        return $this->onEvent($e);
    }

    /**
     *
     * @param \Zend\EventManager\Event $e
     */
    public function onUpdate(Event $e)
    {
        return $this->onEvent($e);

    }

    /**
     *
     * @param \Zend\EventManager\Event $e
     *
     * @throws \MessageExchangeEventManager\Exception\InvalidParamException
     * @throws \MessageExchangeEventManager\Exception\ListenerRequirementException
     */
    public function onEvent(Event $e)
    {

        $request = $e->getRequest();
        $response = $e->getResponse();

        try {
            $query = $request->getParameters()->get('query');
            $id = $request->getParameters()->get('id');
            $identifierName = $request->getParameters()->get('identifierName');
            if (empty($query) || !$query instanceof AbstractPreparableSql) {
                throw new ListenerRequirementException('parametro query non presente: possibile errore nella sequenza dei listener ',
                                                       500);
            }
            if (empty($id)) {
                throw new InvalidParamException('parametro id non valido', 422);
            }
            $this->composeWhere($query, $id, $identifierName);
            $request->getParameters()->set('hasConstraintWhere', true);
            $request->getParameters()->set('constraint', [$identifierName => $id]);

        } catch (\Exception $error) {
            $response->setcontent($error);
            $e->stopPropagation();
        }

        return $response;

    }

    /**
     * metodo di costruzione della where
     * per tutti gli eventi gestiti
     *
     * nel comportamento standard la query viene filtrata per 'id'=$id
     *
     * @param \Zend\Db\Sql\AbstractPreparableSql $query
     * @param                                    $id
     */
    protected function composeWhere(AbstractPreparableSql $query, $id, $identifierName)
    {

        $query->where(function (Where $where) use ($id, $identifierName) {
            $where->and;
            $nest = $where->nest();
            $predicate = new Operator($identifierName, Operator::OP_EQ, $id);
            $nest->addPredicate($predicate, Predicate::OP_AND);
        });
    }

}
