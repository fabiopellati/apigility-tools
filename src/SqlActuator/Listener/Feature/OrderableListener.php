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

use ApigilityTools\SqlActuator\Listener\SqlActuatorListenerInterface;
use MessageExchangeEventManager\Event\Event;
use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;

class OrderableListener
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
            $events->attach(SqlActuatorListenerInterface::EVENT_PRE_SQL_SELECT, [$this, 'onEvent'], $priority);
    }

    /**
     *
     * @param \MessageExchangeEventManager\Event\Event $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     * @internal  \MessageExchangeEventManager\Request\RequestInterface
     */
    public function onEvent(Event $e)
    {
        $request = $e->getRequest();
        $params = $request->getParameters()->get('paramsFromQuery');
        $response = $e->getResponse();
        if (empty($params['order'])) {
            return $response;
        }
        if (empty($params['order_direction'])) {
            $params['order_direction'] = 'ASC';
        }
        try {
            $query = $request->getParameters()->get('query');
            $order = $params['order'];
            $order_direction = $params['order_direction'];
            $order = [$order => strtoupper($order_direction)];
            $query->order($order);

        } catch (\Exception $error) {
            $response->setContent($error);
            $e->stopPropagation();
        }

        return $response;
    }
}
