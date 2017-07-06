<?php
/**
 * Created by PhpStorm.
 * User: fabio
 * Date: 19/04/17
 * Time: 11.43
 */

namespace ApigilityTools\Listener;

use ApigilityTools\Mapper\SqlActuatorMapper;
use ApigilityTools\Paginator\Adapter\PreLimitedArrayAdapter;
use MessageExchangeEventManager\Event\EventInterface;
use MessageExchangeEventManager\Exception\ListenerRequirementException;
use MessageExchangeEventManager\Request\Request;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;

class HydratorPreLimitedDbResultsetCollectionListener
    extends AbstractListenerAggregate
{


    /**
     * @param \Zend\EventManager\EventManagerInterface $events
     * @param int                                      $priority
     *
     */
    public function attach(EventManagerInterface $events, $priority = 100)
    {

        $this->listeners[] = $events->attach(SqlActuatorMapper::EVENT_MAPPER_POST_FETCH_ALL,
                                             [$this, 'onRunPostFetchAll'], $priority);

    }


    /**
     * @param \MessageExchangeEventManager\Event\EventInterface $e
     *
     * @return \MessageExchangeEventManager\Response\Response
     */
    public function onRunPostFetchAll(EventInterface $e)
    {
        $request = $e->getRequest();

        $response = $e->getResponse();
        $collectionClass = $this->getCollectionClassParam($request);
        /**
         * @var \Zend\Db\Adapter\Driver\ResultInterface
         */
        $content = $response->getContent();
        $count = $this->getCountAffectedParam($request);
//        print_r([__METHOD__=>(int)$count]);

        if ($content instanceof HydratingResultSet) {

            $adapter = new PreLimitedArrayAdapter($content->toArray(), (int)$count);
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
    private function getCountAffectedParam(Request $request)
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
    private function getCollectionClassParam(Request $request)
    {
        $collectionClass = $request->getParameters()->get('collectionClass');
        if (empty($collectionClass)) {
            throw new ListenerRequirementException('parametro collectionClass non presente: possibile errore nella sequenza dei listener ',
                                                   500);
        }

        return $collectionClass;

    }

}