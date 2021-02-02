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

namespace ApigilityTools\SqlActuator\Hydrator;

use ApigilityTools\Mapper\Mapper;
use MessageExchangeEventManager\Event\EventInterface;
use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;

class HydratorResultsetListener
    extends AbstractListenerAggregate
{

    /**
     * @param \Laminas\EventManager\EventManagerInterface $events
     * @param int                                      $priority
     *
     */
    public function attach(EventManagerInterface $events, $priority = 100)
    {

        $this->listeners[] = $events->attach(Mapper::EVENT_MAPPER_POST_CREATE, [$this, 'onRunPost'],
                                             $priority);
        $this->listeners[] = $events->attach(Mapper::EVENT_MAPPER_POST_FETCH, [$this, 'onRunPost'],
                                             $priority);
        $this->listeners[] = $events->attach(Mapper::EVENT_MAPPER_POST_DELETE, [$this, 'onRunPost'],
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
        $hydrator = $request->getParameters()->get('hydrator');
        $resultset = $request->getParameters()->get('resultset');
        $response = $e->getResponse();
        $content = $response->getContent();
        if ($content) {
            $resultset = $hydrator->hydrate($content, $resultset);
            $response->setContent($resultset);
        }

        return $response;
    }

}