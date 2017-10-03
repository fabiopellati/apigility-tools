<?php
/**
 * Created by PhpStorm.
 * User: fabio
 * Date: 22/02/17
 * Time: 17.44
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
     * @var array
     */
    protected $params;

    /**
     * @var array
     */
    protected $affected;

    /**
     * RestSearchableListenerAggregate constructor.
     *
     * @param array $params
     * @param array $affected
     */
    function __construct(array $params, array $affected = [])
    {
        $this->params = $params;
        $this->affected = $affected;
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

        $this->listeners[] = $events->attach(SqlActuatorListenerInterface::EVENT_PRE_SQL_SELECT, [$this, 'onEvent'], $priority);
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
        $params = $this->params;
        $request = $e->getRequest();
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
                    if (count($this->affected) > 0 && !isset($affected[$key])) {
                        continue;
                    }
                    if ($value != '') {
                        $nest = $where->NEST;
                        $nest->like($key, $value);
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