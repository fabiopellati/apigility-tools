<?php
/**
 * Created by PhpStorm.
 * User: fabio
 * Date: 19/04/17
 * Time: 11.43
 */

namespace ApigilityTools\SqlActuator\Listener;

use ApigilityTools\Mapper\Mapper;
use ApigilityTools\Traits\ReplaceEventAwareTrait;
use MessageExchangeEventManager\Event\EventInterface;
use MessageExchangeEventManager\EventManagerAwareTrait;
use MessageExchangeEventManager\EventRunAwareTrait;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\TableIdentifier;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;

class SqlListener
    extends AbstractListenerAggregate
    implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;
    use EventRunAwareTrait;


    /**
     * @param \Zend\EventManager\EventManagerInterface $events
     * @param int                                      $priority
     *
     */
    public function attach(EventManagerInterface $events, $priority = 500)
    {

        $this->listeners[] = $events->attach(Mapper::EVENT_MAPPER_PRE_FETCH, [$this, 'onEvent'], $priority);
        $this->listeners[] = $events->attach(Mapper::EVENT_MAPPER_PRE_FETCH_ALL, [$this, 'onEvent'], $priority);
        $this->listeners[] = $events->attach(Mapper::EVENT_MAPPER_PRE_UPDATE, [$this, 'onEvent'], $priority);
        $this->listeners[] = $events->attach(Mapper::EVENT_MAPPER_PRE_DELETE, [$this, 'onEvent'], $priority);
        $this->listeners[] = $events->attach(Mapper::EVENT_MAPPER_PRE_CREATE, [$this, 'onEvent'], $priority);
        $this->listeners[] = $events->attach(Mapper::EVENT_MAPPER_PRE_PATCH, [$this, 'onEvent'], $priority);

    }

    /**
     * @param \MessageExchangeEventManager\Event\EventInterface $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     */
    public function onEvent(EventInterface $e)
    {

        $request = $e->getRequest();
        $response = $e->getResponse();
        $dbAdapter = $request->getParameters()->get('dbAdapter');
        $dbSchema = $request->getParameters()->get('dbSchema');
        $dbTable = $request->getParameters()->get('dbTable');
        $sql = new Sql($dbAdapter, new TableIdentifier($dbTable, $dbSchema));
        $request->getParameters()->set('sql', $sql);

        return $response;
    }


}