<?php
/**
 * Created by PhpStorm.
 * User: fabio
 * Date: 22/02/17
 * Time: 17.44
 */

namespace ApigilityTools\Listener\Sql;

use MessageExchangeEventManager\Event\Event;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;

class SqlSearchableListener
    extends AbstractListenerAggregate
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
     *
     * aggiunge alla select del mapper una serie di where ricavandoli dai parametri search e search_into
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
        if (empty($params['search_into']) || empty($params['search'])) {
            return $response;
        }
        try {
            $query = $request->getParameters()->get('query');

            $query->where(function ($where) use ($params) {
                $search = $params['search'];
                $nest = $where->NEST;
                if (is_string($params['search_into'])) {
                    $nest->like($params['search_into'], "%$search%");
                    $nest->or;
                } else {
                    if (is_array($params['search_into'])) {
                        foreach ($params['search_into'] as $into) {
                            $nest->like($into, "%$search%");
                            $nest->or;
                        }
                    }
                }
            });
        } catch (\Exception $error) {
            $response->setError($error->getMessage(), $error->getCode());
            $e->stopPropagation();
        }

        return $response;
    }
}

