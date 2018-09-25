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
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;

class FilterTextListener
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
        if (empty($params['filters_keys']) || empty($params['filters_values'])) {
            return $response;
        }
        try {
            $query = $request->getParameters()->get('query');
            $query->where(function ($where) use ($params) {
                $keys = $params['filters_keys'];
                $values = $params['filters_values'];
                foreach ($values as $index => $value) {
                    $key = $keys[$index];
                    if ($value != '') {
                        $nest = $where->NEST;
                        $nest->equalTo($key, $value);
                        $nest->and;
                    }
                }
            });

        } catch (\Exception $error) {
            $response->setContent($error);
            $e->stopPropagation();
        }

        return $response;
    }
}
