<?php
/**
 * Created by PhpStorm.
 * User: fabio
 * Date: 22/02/17
 * Time: 17.44
 */

namespace ApigilityTools\Listener\Sql;

use MessageExchangeEventManager\Event\Event;
use MessageExchangeEventManager\Exception\InvalidParamException;
use MessageExchangeEventManager\Exception\ListenerRequirementException;
use Zend\Db\Sql\Select;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;

class SqlPaginatorListener
    extends AbstractListenerAggregate
{


    /**
     *
     *
     * @param EventManagerInterface $events
     * @param int                   $priority
     *
     * @return void
     */
    public function attach(EventManagerInterface $events, $priority = 100)
    {

        $this->listeners[] = $events->attach(SqlActuatorListener::EVENT_SQL_SELECT, [$this, 'onEvent'],
                                             $priority + 100);
    }

    /**
     *
     * @param \MessageExchangeEventManager\Event\Event $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     * @throws \MessageExchangeEventManager\Exception\InvalidParamException
     * @internal  \MessageExchangeEventManager\Request\RequestInterface
     */
    public function onEvent(Event $e)
    {
        $request = $e->getRequest();
        $response = $e->getResponse();
        $requestQuery = $request->getParameters()->get('request_query');

        if (empty($requestQuery) || empty($requestQuery['page'])) {
            return $response;
        }
        $pageSize = (int)$this->getPageSize($request, $requestQuery);
        $page = (int)$requestQuery['page'];
        try {
            /**
             * @var $query \Zend\Db\Sql\Select
             */
            $query = $request->getParameters()->get('query');
            if (!$query instanceof Select) {
                return $response;
            }

            $query->limit($pageSize);
            $query->offset(($page * $pageSize) - $pageSize);
        } catch (\Exception $error) {
            $response->setError($error->getMessage(), $error->getCode());
            $e->stopPropagation();
        }

//        print_r([__METHOD__=>$request->getParameters()->get('query')->getSqlString()]);
        return $response;
    }

    /**
     * @param $request
     * @param $requestQuery
     *
     * @return array
     * @throws \MessageExchangeEventManager\Exception\InvalidParamException
     * @throws \MessageExchangeEventManager\Exception\ListenerRequirementException
     */
    private function getPageSize($request, $requestQuery)
    {
        $apigilityConfig = $request->getParameters()->get('apigilityConfig');
        if (empty($apigilityConfig)) {
            throw new ListenerRequirementException(__CLASS__ . '. apigilityConfig is missed', 500);
        }
        $pageSizeParam = $apigilityConfig['page_size_param'];

        $pageSize = (int)$apigilityConfig['page_size'];
        $pageSize = (!empty($pageSizeParam) && !empty($requestQuery[$pageSizeParam])) ? $requestQuery[$pageSizeParam] :
            $pageSize;
        if (empty($pageSize)) {
            throw new InvalidParamException(__CLASS__ . '. page_size is missed', 500);
        }

        return $pageSize;
    }
}

