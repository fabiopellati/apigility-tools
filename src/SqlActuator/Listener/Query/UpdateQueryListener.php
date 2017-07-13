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
use ApigilityTools\SqlActuator\Listener\Sql\SqlActuatorListenerInterface;
use MessageExchangeEventManager\Event\Event;
use Zend\Db\Sql\Sql;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;

class UpdateQueryListener
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

        $this->listeners[] = $events->attach(SqlActuatorListenerInterface::EVENT_PRE_SQL_UPDATE, [$this, 'onEvent'], $priority);
    }


    /**
     *
     * @param \MessageExchangeEventManager\Event\Event|\Zend\EventManager\Event $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
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
            $query = $sql->update();
            $request->getParameters()->set('query', $query);

        } catch (\Exception $error) {
            $response->setcontent($error);
            $e->stopPropagation();
        }

        return $response;
    }


}