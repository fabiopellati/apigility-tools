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

namespace ApigilityTools\SqlActuator\Listener;

use ApigilityTools\Mapper\Mapper;
use ApigilityTools\Traits\ReplaceEventAwareTrait;
use MessageExchangeEventManager\Event\EventInterface;
use MessageExchangeEventManager\EventManagerAwareTrait;
use MessageExchangeEventManager\EventRunAwareTrait;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;

class PatchListener
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

        $this->listeners[] = $events->attach(Mapper::EVENT_MAPPER_PATCH, [$this, 'onMapperEvent'],
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
        $event->getRequest()->getParameters()->set('mapper_action', Mapper::EVENT_MAPPER_PATCH);
        $response = $this->runEvent($event, SqlActuatorListenerInterface::EVENT_PRE_SQL_PATCH,
                                    SqlActuatorListenerInterface::EVENT_SQL_PATCH,
                                    SqlActuatorListenerInterface::EVENT_POST_SQL_PATCH);

        return $response;
    }

}