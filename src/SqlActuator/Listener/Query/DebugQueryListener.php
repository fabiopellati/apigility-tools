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
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;

class DebugQueryListener
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
            $events->attach(SqlActuatorListenerInterface::EVENT_SQL_SELECT, [$this, 'onEvent'], $priority);
        $this->listeners[] =
            $events->attach(SqlActuatorListenerInterface::EVENT_SQL_INSERT, [$this, 'onEvent'], $priority);
        $this->listeners[] =
            $events->attach(SqlActuatorListenerInterface::EVENT_SQL_UPDATE, [$this, 'onEvent'], $priority);
        $this->listeners[] =
            $events->attach(SqlActuatorListenerInterface::EVENT_SQL_DELETE, [$this, 'onEvent'], $priority);
    }

    /**
     * @param \MessageExchangeEventManager\Event\Event $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     */
    public function onEvent(Event $e)
    {
        $response = $e->getResponse();
        try {
            $request = $e->getRequest();
            /**
             * @var \Zend\Db\Sql\Select $query
             */
            $query = $request->getParameters()->get('query');
            $sqlString = $query->getSqlString();
            throw new \Exception($sqlString, 599);
        } catch (\Exception $error) {
            $response->setContent($error);
            $e->stopPropagation();
        }

        return $response;
    }

}
