<?php
/**
 * Created by PhpStorm.
 * User: fabio
 * Date: 19/04/17
 * Time: 11.43
 */

namespace ApigilityTools\SqlActuator\Listener\Sql;

use ApigilityTools\Mapper\Mapper;
use MessageExchangeEventManager\Event\EventInterface;
use MessageExchangeEventManager\EventManagerAwareTrait;
use MessageExchangeEventManager\EventRunAwareTrait;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;

class SqlCreateListener
    extends AbstractListenerAggregate
    implements SqlActuatorListenerInterface, EventManagerAwareInterface
{
    use EventManagerAwareTrait;
    use EventRunAwareTrait;
    use ReplaceEventAwareTrait;

    /**
     * @param \Zend\EventManager\EventManagerInterface $events
     * @param int                                      $priority
     *
     */
    public function attach(EventManagerInterface $events, $priority = 100)
    {

        $this->listeners[] = $events->attach(Mapper::EVENT_MAPPER_CREATE, [$this, 'onMapperEvent'],
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
        $event->getRequest()->getParameters()->set('mapper_action', Mapper::EVENT_MAPPER_CREATE);
        $response = $this->runEvent($event, SqlActuatorListenerInterface::EVENT_PRE_SQL_INSERT,
                                    SqlActuatorListenerInterface::EVENT_SQL_INSERT,
                                    SqlActuatorListenerInterface::EVENT_POST_SQL_INSERT);

        return $response;
    }


}