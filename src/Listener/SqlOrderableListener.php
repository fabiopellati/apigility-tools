<?php
/**
 * Created by PhpStorm.
 * User: fabio
 * Date: 22/02/17
 * Time: 17.44
 */

namespace ApigilityTools\Listener;

use ApigilityTools\Listener\Sql\SqlActuatorListener;
use MessageExchangeEventManager\Event\Event;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;

class SqlOrderableListener extends AbstractListenerAggregate
{

    /**
     * @var array
     */
    protected $params;

    /**
     * RestSearchableListenerAggregate constructor.
     *
     * @param array $params
     */
    function __construct(array $params)
    {
        $this->params = $params;
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

        $this->listeners[] = $events->attach(SqlActuatorListener::EVENT_PRE_SQL_SELECT, [$this, 'onEvent'], $priority);
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
            $response->setError($error->getMessage(), $error->getCode());
            $e->stopPropagation();
        }
        return $response;
    }
}