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
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Paginator\Adapter\ArrayAdapter;

class HydratorDbResultsetCollectionListener extends AbstractListenerAggregate
{


    /**
     * @param \Zend\EventManager\EventManagerInterface $events
     * @param int                                      $priority
     *
     */
    public function attach(EventManagerInterface $events, $priority = 100)
    {

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
        $collectionClass = $request->getParameters()->get('collectionClass');
        /**
         * @var \Zend\Db\Adapter\Driver\ResultInterface
         */
        $content = $response->getContent();

        if ($content instanceof HydratingResultSet) {
            $adapter = new ArrayAdapter($content->toArray());
            $collection = new $collectionClass($adapter);
            $response->setContent($collection);
        }
        return $response;
    }


}