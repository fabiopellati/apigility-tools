<?php
/**
 * Created by PhpStorm.
 * User: fabio
 * Date: 22/02/17
 * Time: 17.44
 */

namespace ApigilityTools\SqlActuator\Listener\Feature;

use ApigilityTools\Exception\RuntimeException;
use ApigilityTools\SqlActuator\Listener\SqlActuatorListenerInterface;
use MessageExchangeEventManager\Event\Event;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;

class AssociationListener extends AbstractListenerAggregate
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
     *
     * @param array $params
     * @param array $affected
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
     * @param int $priority
     *
     * @return void
     */
    public function attach(EventManagerInterface $events, $priority = 50)
    {

        $this->listeners[] =
            $events->attach(SqlActuatorListenerInterface::EVENT_PRE_SQL_SELECT, [$this, 'onEvent'], $priority);
    }

    /**
     *
     * @param \MessageExchangeEventManager\Event\Event $e
     *
     * @return \MessageExchangeEventManager\Response\Response
     * @throws \ApigilityTools\Exception\RuntimeException
     * @internal  \ChainEvent\Request\RequestInterface
     */
    public function onEvent(Event $e)
    {
        $params = $this->params;
        $request = $e->getRequest();
        $response = $e->getResponse();
        $routeAssociationIdentifierName=$request->getParameters()->get('routeAssociationIdentifierName');
        $entityAssociationIdentifierName=$request->getParameters()->get('entityAssociationIdentifierName');

        if (empty($params[$routeAssociationIdentifierName])) {
            throw new RuntimeException('Entity not fount', 404);
        }
        try {
            $query = $request->getParameters()->get('query');

                $id = $params[$routeAssociationIdentifierName];
            $query->where(function ($where) use ($id, $entityAssociationIdentifierName) {
                        $nest = $where->NEST;
                        $nest->equalTo($entityAssociationIdentifierName, $id);
                        $nest->and;
            });

        } catch (\Exception $error) {
            $response->setError($error->getMessage(), $error->getCode());
            $e->stopPropagation();
        }
        return $response;
    }
}