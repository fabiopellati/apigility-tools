<?php
/**
 * Created by PhpStorm.
 * User: fabio
 * Date: 19/04/17
 * Time: 11.43
 */

namespace ApigilityTools\Listener;

use ApigilityTools\Mapper\SqlActuatorMapper;
use MessageExchangeEventManager\Event\EventInterface;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;

class HydratorResultsetListener extends AbstractListenerAggregate
{


    /**
     * @param \Zend\EventManager\EventManagerInterface $events
     * @param int                                      $priority
     *
     */
    public function attach(EventManagerInterface $events, $priority = 1000)
    {

        $this->listeners[] = $events->attach(SqlActuatorMapper::EVENT_MAPPER_POST_CREATE, [$this, 'onRunPost'],
                                             $priority);
        $this->listeners[] = $events->attach(SqlActuatorMapper::EVENT_MAPPER_POST_FETCH, [$this, 'onRunPost'],
                                             $priority);
        $this->listeners[] = $events->attach(SqlActuatorMapper::EVENT_MAPPER_POST_DELETE, [$this, 'onRunPost'],
                                             $priority);

    }


    /**
     * @param \MessageExchangeEventManager\Event\EventInterface $e
     *
     * @return \MessageExchangeEventManager\Response\Response
     */
    public function onRunPost(EventInterface $e)
    {
        $request = $e->getRequest();
        $hydrator = $request->getHydrator();
        $resultset = $e->getRequest()->getResultset();
        $response = $e->getResponse();

        $content = $response->getContent();

        if ($content) {
            $resultset = $hydrator->hydrate($content, $resultset);
            $response->setContent($resultset);
        }
        return $response;
    }


}