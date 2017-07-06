<?php
/**
 * Created by PhpStorm.
 * User: fabio
 * Date: 06/07/17
 * Time: 16.23
 */

namespace ApigilityTools\Listener\Sql;

use MessageExchangeEventManager\Event\EventInterface;
use Zend\EventManager\Event;

trait ReplaceEventAwareTrait
{
    /**
     * @param \MessageExchangeEventManager\Event\EventInterface $e
     *
     * @return \MessageExchangeEventManager\Event\Event
     */
    protected function replaceEvent(EventInterface $e)
    {
        $event = new Event();
        $event->setRequest($e->getRequest());
        $event->setResponse($e->getResponse());
        $event->getTarget($this);

        return $event;
    }
}