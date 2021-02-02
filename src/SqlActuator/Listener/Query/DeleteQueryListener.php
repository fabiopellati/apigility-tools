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

namespace ApigilityTools\SqlActuator\Listener\Query;

use ApigilityTools\Exception\InvalidParamException;
use ApigilityTools\SqlActuator\Listener\SqlActuatorListenerInterface;
use MessageExchangeEventManager\Event\Event;
use Laminas\Db\Sql\Sql;
use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;

class DeleteQueryListener
    extends AbstractListenerAggregate
{

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
    public function attach(EventManagerInterface $events, $priority = 1000)
    {

        $this->listeners[] =
            $events->attach(SqlActuatorListenerInterface::EVENT_PRE_SQL_DELETE, [$this, 'onEvent'], $priority);
    }

    /**
     *
     * @param \MessageExchangeEventManager\Event\Event $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     */
    public function onEvent(Event $e)
    {
        $request = $e->getRequest();
        $response = $e->getResponse();
        try {
            $sql = $request->getParameters()->get('sql');
            if (!$sql instanceof Sql) {
                throw new InvalidParamException('parametro sql non valido', 500);
            }
            $query = $sql->delete();
            $request->getParameters()->set('query', $query);

        } catch (\Exception $error) {
            $response->setContent($error);
            $e->stopPropagation();
        }

        return $response;
    }

}
