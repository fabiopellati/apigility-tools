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
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Paginator\Adapter\ArrayAdapter;

class HydratorDbResultsetCollectionListener
    extends AbstractListenerAggregate
{

    /**
     * @param \Laminas\EventManager\EventManagerInterface $events
     * @param int                                      $priority
     *
     */
    public function attach(EventManagerInterface $events, $priority = 100)
    {

        $this->listeners[] = $events->attach(Mapper::EVENT_MAPPER_POST_FETCH_ALL, [$this, 'onRunPostFetch'],
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
         * @var \Laminas\Db\Adapter\Driver\ResultInterface
         */
        $content = $response->getContent();
        if ($content instanceof HydratingResultSet) {
            $adapter = new ArrayAdapter($content);
            $collection = new $collectionClass($adapter);
            $response->setContent($collection);
        }

        return $response;
    }

}