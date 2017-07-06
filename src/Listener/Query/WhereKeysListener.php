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

namespace ApigilityTools\Listener\Query;

use ApigilityTools\Exception\InvalidParamException;
use ApigilityTools\Listener\Sql\SqlActuatorListener;
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
     * @var string
     */
    protected $identifierDelimiter = '_';

    /**
     * @var array
     */
    protected $keys = [];

    /**
     * Must be an array, it can also contain a single element
     *
     * @param array $keys
     */
    public function setKeys(array $keys)
    {
        $this->keys = $keys;
    }

    /**
     * @param string $identifierDelimiter
     */
    public function setIdentifierDelimiter($identifierDelimiter)
    {
        $this->identifierDelimiter = $identifierDelimiter;
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
    public function attach(EventManagerInterface $events, $priority = 100)
    {

        $this->listeners[] = $events->attach(SqlActuatorListener::EVENT_PRE_SQL_DELETE, [$this, 'onDelete'], $priority);
        $this->listeners[] = $events->attach(SqlActuatorListener::EVENT_PRE_SQL_SELECT, [$this, 'onSelect'], $priority);
        $this->listeners[] = $events->attach(SqlActuatorListener::EVENT_PRE_SQL_UPDATE, [$this, 'onUpdate'], $priority);
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
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     */
    public function onEvent(Event $e)
    {

        $request = $e->getRequest();
        $response = $e->getResponse();
        try {
            $id = $request->getParameters()->get('id');

            if (!empty($identifierName)) {
                $this->keys = explode($this->identifierDelimiter, $identifierName);
            }
            $identifierName = $request->getParameters()->get('identifierName');
            if (!empty($identifierName)) {
                $this->keys = explode($this->identifierDelimiter, $identifierName);
            }

            $keys_value = explode($this->identifierDelimiter, $id);
            if (count($keys_value) != count($this->keys)) {
                throw new InvalidParamException('Id parameter contains a wrong number of elements', 500);
            }
            $query = $request->getParameters()->get('query');
            if (empty($query) || !$query instanceof AbstractPreparableSql) {
                throw new ListenerRequirementException('parametro query non presente: possibile errore nella sequenza dei listener ',
                                                       500);
            }

            $this->composeWhere($query, $keys_value);
            $request->getParameters()->set('hasConstraintWhere', true);
            $request->getParameters()->set('constraint', [$identifierName => $id]);

        } catch (\Exception $error) {
            $response->setError($error->getMessage(), $error->getCode());
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
     * @param                       $keys_values
     *
     * @internal param $id
     */
    protected function composeWhere(AbstractPreparableSql $query, $keys_values)
    {

        $keys = $this->keys;
        $query->where(function (Where $where) use ($keys, $keys_values) {
            $where->and;
            $nest = $where->nest();
            foreach ($keys as $index => $key) {
                $value = $keys_values[$index];
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
