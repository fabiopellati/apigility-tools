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

namespace ApigilityTools\SqlActuator\Listener\Feature;

use ApigilityTools\SqlActuator\Listener\SqlActuatorListenerInterface;
use MessageExchangeEventManager\Event\Event;
use MessageExchangeEventManager\Exception\InvalidParamException;
use MessageExchangeEventManager\Exception\ListenerRequirementException;
use Laminas\Db\Sql\Select;
use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;

class PaginatorListener
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

        $this->listeners[] = $events->attach(SqlActuatorListenerInterface::EVENT_PRE_SQL_SELECT, [$this, 'onEvent'],
                                             $priority);
    }

    /**
     *
     * @param \MessageExchangeEventManager\Event\Event $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     * @throws \MessageExchangeEventManager\Exception\InvalidParamException
     * @throws \MessageExchangeEventManager\Exception\ListenerRequirementException
     * @internal  \MessageExchangeEventManager\Request\RequestInterface
     */
    public function onEvent(Event $e)
    {
        $request = $e->getRequest();
        $response = $e->getResponse();
        $requestQuery = $request->getParameters()->get('request_query');
        if (empty($requestQuery) ||
            empty($requestQuery['page'])
        ) {
            $page = 1;
        } else {
            $page = (int)$requestQuery['page'];
        }
        $pageSize = (int)$this->getPageSize($request, $requestQuery);
        try {
            /**
             * @var $query \Laminas\Db\Sql\Select
             */
            $query = $request->getParameters()->get('query');
            if (!$query instanceof Select) {
                return $response;
            }
            $query->limit($pageSize);
            $query->offset(($page * $pageSize) - $pageSize);
        } catch (\Exception $error) {
            $response->setContent($error);
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
    protected function getPageSize($request, $requestQuery)
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

