<?php
/**
 * Created by PhpStorm.
 * User: fabio
 * Date: 19/04/17
 * Time: 11.43
 */

namespace Gnc\ApigilityTools\Listener\Sql;

use ApigilityTools\Listener\Sql\ReplaceEventAwareTrait;
use ApigilityTools\Mapper\SqlActuatorMapper;
use MessageExchangeEventManager\Event\EventInterface;
use MessageExchangeEventManager\EventManagerAwareTrait;
use MessageExchangeEventManager\EventRunAwareTrait;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;

class SqlFetchListener
    extends AbstractListenerAggregate
    implements SqlActuatorListenerInterface, EventManagerAwareInterface
{
    use EventManagerAwareTrait;
    use EventRunAwareTrait;
    use ReplaceEventAwareTrait;

    /**
     * @param \Zend\EventManager\EventManagerInterface $events
     * @param int $priority
     *
     */
    public function attach(EventManagerInterface $events, $priority = 1000)
    {

        $this->listeners[] = $events->attach(SqlActuatorMapper::EVENT_MAPPER_FETCH, [$this, 'onMapperEvent'],
                                             $priority);

    }


    /**
     * @param \MessageExchangeEventManager\Event\EventInterface $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     */
    public function onMapperEvent(EventInterface $e)
    {

        $event = $this->replaceEvent($e);
        $event->getRequest()->getParameters()->set('mapper_action', SqlActuatorMapper::EVENT_MAPPER_FETCH);
        $response = $this->runEvent($event, SqlActuatorListenerInterface::EVENT_PRE_SQL_SELECT,
                                    SqlActuatorListenerInterface::EVENT_SQL_SELECT,
                                    SqlActuatorListenerInterface::EVENT_POST_SQL_SELECT);
        return $response;
    }


}