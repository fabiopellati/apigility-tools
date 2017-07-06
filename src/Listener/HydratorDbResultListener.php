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
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;

class HydratorDbResultListener
    extends AbstractListenerAggregate
{


    /**
     * @param \Zend\EventManager\EventManagerInterface $events
     * @param int                                      $priority
     *
     */
    public function attach(EventManagerInterface $events, $priority = 100)
    {

        $this->listeners[] = $events->attach(SqlActuatorMapper::EVENT_MAPPER_POST_CREATE, [$this, 'onRunPostFetch'],
                                             $priority);
        $this->listeners[] = $events->attach(SqlActuatorMapper::EVENT_MAPPER_POST_FETCH, [$this, 'onRunPostFetch'],
                                             $priority);
        $this->listeners[] = $events->attach(SqlActuatorMapper::EVENT_MAPPER_POST_FETCH_ALL, [$this, 'onRunPostFetch'],
                                             $priority);

    }


    /**
     * @param \MessageExchangeEventManager\Event\EventInterface $e
     *
     * @return \MessageExchangeEventManager\Response\Response
     */
    public function onRunPostFetch(EventInterface $e)
    {
        $request = $e->getRequest();

        $response = $e->getResponse();

        /**
         * @var \Zend\Db\Adapter\Driver\ResultInterface
         */
        $content = $response->getContent();

        if ($content instanceof ResultInterface && $content->isQueryResult()) {
            $hydrator = $request->getParameters()->get('hydrator');
            $resultset = $request->getParameters()->get('resultset');

            $resultset = new HydratingResultSet($hydrator, $resultset);
            $resultset->initialize($content);
            $response->setContent($resultset);
        }

        return $response;
    }


}