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
use MessageExchangeEventManager\Event\Event;
use MessageExchangeEventManager\Exception\ListenerRequirementException;
use Zend\Db\Sql\AbstractPreparableSql;
use Zend\Db\Sql\Predicate\Like;
use Zend\Db\Sql\Predicate\Operator;
use Zend\Db\Sql\Predicate\Predicate;
use Zend\Db\Sql\Where;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;

class WhereKeysListener
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
     * @param \MessageExchangeEventManager\Event\Event $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
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
     * @param \MessageExchangeEventManager\Event\Event $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     */
    public function onDelete(Event $e)
    {
        return $this->onEvent($e);
    }

    /**
     *
     * @param \MessageExchangeEventManager\Event\Event $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     */
    public function onUpdate(Event $e)
    {
        return $this->onEvent($e);

    }


    /**
     * @param \MessageExchangeEventManager\Event\Event $e
     *
     * @todo linea 116 questa eccezione non ha effetti sulla cascata
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     */
    public function onEvent(Event $e)
    {

        $request = $e->getRequest();
        $response = $e->getResponse();
        try {
            $id = $request->getParameters()->get('id');
            $identifierDelimiter = $request->getParameters()->get('identifierDelimiter');
            if (!empty($identifierDelimiter)) {
                $identifierDelimiter = '_';
            }
            $identifierName = $request->getParameters()->get('identifierName');
            $keys=[];
            if (!empty($identifierName)) {
                $keys = explode($identifierDelimiter, $identifierName);
            }

            $values = explode($identifierDelimiter, $id);
            if (count($values) != count($keys)) {
                throw new InvalidParamException('Id parameter contains a wrong number of elements', 500);
            }
            $query = $request->getParameters()->get('query');
            if (empty($query) || !$query instanceof AbstractPreparableSql) {
                throw new ListenerRequirementException('parametro query non presente: possibile errore nella sequenza dei listener ',
                                                       500);
            }

            $this->composeWhere($query, $keys, $values);
            $request->getParameters()->set('hasConstraintWhere', true);
            $composedKey=array_combine($keys,$values);
            $request->getParameters()->set('composedKey', $composedKey);
            $request->getParameters()->set('constraint', [$identifierName => $id]);

        } catch (\Exception $error) {
            $response->setcontent($error);
            $e->stopPropagation();
        }

        return $response;

    }

    /**
     * metodo di costruzione della where con chiave composita
     * per tutti gli eventi gestiti
     *
     * nel comportamento standard la query viene filtrata per  con una nest and
     * 'chiave'=valore per ciascuna chiave definita
     *
     * @param AbstractPreparableSql $query
     * @param                       $values
     *
     * @internal param $id
     */
    protected function composeWhere(AbstractPreparableSql $query,$keys, $values)
    {


        $query->where(function (Where $where) use ($keys, $values) {
            $where->and;
            $nest = $where->nest();
            foreach ($keys as $index => $key) {
                $value = $values[$index];
                if ($value === (int)$value) {
                    $value = (int)$value;

                    $predicate = new Operator($key, Operator::OP_EQ, $value);
                    $nest->addPredicate($predicate, Predicate::OP_AND);
                    continue;
                }
                $predicate = new Like($key, $value);
                $nest->addPredicate($predicate, Predicate::OP_AND);

            }
        });
    }

}
