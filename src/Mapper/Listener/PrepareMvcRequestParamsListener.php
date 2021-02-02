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

namespace ApigilityTools\Mapper\Listener;

use ApigilityTools\Mapper\Mapper;
use MessageExchangeEventManager\Event\Event;
use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;

/**
 *
 */
class PrepareMvcRequestParamsListener
    extends AbstractListenerAggregate
{

    /**
     * @var \Laminas\Mvc\MvcEvent
     */
    private $mvcEvent;

    /**
     * PrepareMvcRequestParamsListener constructor.
     *
     * @param \Laminas\Mvc\MvcEvent $mvcEvent
     */
    public function __construct(\Laminas\Mvc\MvcEvent $mvcEvent)
    {
        $this->mvcEvent = $mvcEvent;
    }

    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     * @param int                   $priority
     *
     * @return void
     */
    public function attach(EventManagerInterface $events, $priority = 200)
    {

        $this->listeners[] =
            $events->attach(Mapper::EVENT_MAPPER_PRE_UPDATE, [$this, 'onEvent'], $priority);
        $this->listeners[] =
            $events->attach(Mapper::EVENT_MAPPER_PRE_FETCH, [$this, 'onEvent'], $priority);
        $this->listeners[] =
            $events->attach(Mapper::EVENT_MAPPER_PRE_FETCH_ALL, [$this, 'onEvent'], $priority);
        $this->listeners[] =
            $events->attach(Mapper::EVENT_MAPPER_PRE_UPDATE, [$this, 'onEvent'], $priority);
        $this->listeners[] =
            $events->attach(Mapper::EVENT_MAPPER_PRE_CREATE, [$this, 'onEvent'], $priority);
        $this->listeners[] =
            $events->attach(Mapper::EVENT_MAPPER_PRE_PATCH, [$this, 'onEvent'], $priority);
    }

    /**
     * @param \MessageExchangeEventManager\Event\Event $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     */
    public function onEvent(Event $e)
    {

        $request = $e->getRequest();
        $response = $e->getResponse();
        try {
            /**
             * @var $httpRequest \Laminas\Http\Request
             */
            $httpRequest = $this->mvcEvent->getRequest();
            $fromQuery = $httpRequest->getQuery()->toArray();
            $fromPost = $httpRequest->getPost()->toArray();
            $request->getParameters()->set('paramsFromQuery', $fromQuery);
            $request->getParameters()->set('paramsFromPost', $fromPost);

        } catch (\Exception $error) {
            $response->setcontent($error);
            $e->stopPropagation();
        }

        return $response;

    }

}
