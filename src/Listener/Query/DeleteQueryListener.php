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
use Zend\Db\Sql\Sql;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;

class DeleteQueryListener extends AbstractListenerAggregate
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
    public function attach(EventManagerInterface $events, $priority = 10000)
    {

        $this->listeners[] = $events->attach(SqlActuatorListener::EVENT_PRE_SQL_DELETE, [$this, 'onEvent'], $priority);
    }


    /**
     *
     * @param \Zend\EventManager\Event $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     * @throws \MessageExchangeEventManager\Exception\InvalidParamException
     */
    public function onEvent(Event $e)
    {
        $request = $e->getRequest();
        $response = $e->getResponse();

        try {
            $sql = $request->getParameters()->get('sql');
            if (!$sql instanceof Sql) {
                throw new InvalidParamException('parametro sql non valido', 500);
            }
            $query = $sql->delete();
            $request->getParameters()->set('query', $query);

        } catch (\Exception $error) {
            $response->setError($error->getMessage(), $error->getCode());
            $e->stopPropagation();
        }
        return $response;
    }


}
