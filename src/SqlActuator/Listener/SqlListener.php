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

namespace ApigilityTools\SqlActuator\Listener;

use ApigilityTools\Mapper\Mapper;
use MessageExchangeEventManager\Event\EventInterface;
use MessageExchangeEventManager\EventManagerAwareTrait;
use MessageExchangeEventManager\EventRunAwareTrait;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\TableIdentifier;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;

class SqlListener
    extends AbstractListenerAggregate
    implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;
    use EventRunAwareTrait;

    /**
     * @param \Zend\EventManager\EventManagerInterface $events
     * @param int                                      $priority
     *
     */
    public function attach(EventManagerInterface $events, $priority = 500)
    {

        $this->listeners[] = $events->attach(Mapper::EVENT_MAPPER_PRE_FETCH, [$this, 'onEvent'], $priority);
        $this->listeners[] = $events->attach(Mapper::EVENT_MAPPER_PRE_FETCH_ALL, [$this, 'onEvent'], $priority);
        $this->listeners[] = $events->attach(Mapper::EVENT_MAPPER_PRE_UPDATE, [$this, 'onEvent'], $priority);
        $this->listeners[] = $events->attach(Mapper::EVENT_MAPPER_PRE_DELETE, [$this, 'onEvent'], $priority);
        $this->listeners[] = $events->attach(Mapper::EVENT_MAPPER_PRE_CREATE, [$this, 'onEvent'], $priority);
        $this->listeners[] = $events->attach(Mapper::EVENT_MAPPER_PRE_PATCH, [$this, 'onEvent'], $priority);

    }

    /**
     * @param \MessageExchangeEventManager\Event\EventInterface $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     */
    public function onEvent(EventInterface $e)
    {

        $request = $e->getRequest();
        $response = $e->getResponse();
        try {

            $dbAdapter = $request->getParameters()->get('dbAdapter');
            $tableIdentifier = $request->getParameters()->get('tableIdentifier');
            if ($tableIdentifier instanceof TableIdentifier) {
                $sql = new Sql($dbAdapter, $tableIdentifier);
            } else {
                $dbSchema = $request->getParameters()->get('dbSchema');
                $dbTable = $request->getParameters()->get('dbTable');
                $sql = new Sql($dbAdapter, new TableIdentifier($dbTable, $dbSchema));
            }
            $request->getParameters()->set('sql', $sql);
        } catch (\Exception $error) {
            $response->setContent($error);
            $e->stopPropagation();
        }

        return $response;
    }

}
