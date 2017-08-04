<?php
/**
 * Created by PhpStorm.
 * User: fabio
 * Date: 06/07/17
 * Time: 16.23
 */

namespace ApigilityTools\Traits;

use MessageExchangeEventManager\Event\Event;
use MessageExchangeEventManager\Event\EventInterface;

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