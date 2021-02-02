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
use ApigilityTools\Paginator\Adapter\PreLimitedArrayAdapter;
use MessageExchangeEventManager\Event\EventInterface;
use MessageExchangeEventManager\Exception\ListenerRequirementException;
use MessageExchangeEventManager\Request\Request;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;

class HydratorPreLimitedDbResultsetCollectionListener
    extends AbstractListenerAggregate
{

    /**
     * @param \Laminas\EventManager\EventManagerInterface $events
     * @param int                                      $priority
     *
     */
    public function attach(EventManagerInterface $events, $priority = 100)
    {

        $this->listeners[] = $events->attach(Mapper::EVENT_MAPPER_POST_FETCH_ALL,
                                             [$this, 'onRunPostFetchAll'], $priority);

    }

    /**
     * @param \MessageExchangeEventManager\Event\EventInterface $e
     *
     * @return \MessageExchangeEventManager\Response\Response
     * @throws \MessageExchangeEventManager\Exception\ListenerRequirementException
     */
    public function onRunPostFetchAll(EventInterface $e)
    {
        $request = $e->getRequest();
        $response = $e->getResponse();
        $collectionClass = $this->getCollectionClassParam($request);
        /**
         * @var \Laminas\Db\Adapter\Driver\ResultInterface
         */
        $content = $response->getContent();
        $count = $this->getCountAffectedParam($request);
//        print_r([__METHOD__=>(int)$count]);
        if ($content instanceof HydratingResultSet) {

            $adapter = new PreLimitedArrayAdapter($content, (int)$count);
            $collection = new $collectionClass($adapter);
            $response->setContent($collection);
        }

        return $response;
    }

    /**
     * @param \MessageExchangeEventManager\Request\Request $request
     *
     * @return mixed
     * @throws \MessageExchangeEventManager\Exception\ListenerRequirementException
     */
    protected function getCountAffectedParam(Request $request)
    {
        $countAffected = $request->getParameters()->get('count_affected');
        if (empty($countAffected) && !$countAffected === 0) {
            throw new ListenerRequirementException('parametro count_affected non presente: possibile errore nella sequenza dei listener ',
                                                   500);
        }

        return $countAffected;

    }

    /**
     * @param \MessageExchangeEventManager\Request\Request $request
     *
     * @return mixed
     * @throws \MessageExchangeEventManager\Exception\ListenerRequirementException
     */
    protected function getCollectionClassParam(Request $request)
    {
        $collectionClass = $request->getParameters()->get('collectionClass');
        if (empty($collectionClass)) {
            throw new ListenerRequirementException('parametro collectionClass non presente: possibile errore nella sequenza dei listener ',
                                                   500);
        }

        return $collectionClass;

    }

}
