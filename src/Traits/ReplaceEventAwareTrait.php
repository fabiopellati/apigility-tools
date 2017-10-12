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

namespace ApigilityTools\Traits;

use MessageExchangeEventManager\Event\Event;
use MessageExchangeEventManager\Event\EventInterface;

trait ReplaceEventAwareTrait
{
    /**
     * if run event via \MessageExchangeEventManager\EventRunAwareTrait
     * and it's necessary throw a new Event with different name in the same chain this replace event object for new
     * chain without breaking current runEvent task
     *
     * @see \MessageExchangeEventManager\EventRunAwareTrait
     *
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