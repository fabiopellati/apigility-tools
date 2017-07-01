<?php
/**
 * Created by PhpStorm.
 * User: fabio
 * Date: 19/04/17
 * Time: 11.43
 */

namespace ApigilityTools\Listener\Sql;

use ApigilityTools\Mapper\SqlActuatorMapper;
use MessageExchangeEventManager\Event\Event;
use MessageExchangeEventManager\Event\EventInterface;
use MessageExchangeEventManager\EventManagerAwareTrait;
use MessageExchangeEventManager\EventRunAwareTrait;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;

class SqlActuatorListener extends AbstractListenerAggregate implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;
    use EventRunAwareTrait;

    const EVENT_PRE_SQL_CONSTRAINT_WHERE = 'pre.sql.constraint.where';
    const EVENT_PRE_SQL_SELECT = 'pre.sql.select';
    const EVENT_SQL_SELECT = 'sql.select';
    const EVENT_POST_SQL_SELECT = 'post.sql.select';

    const EVENT_PRE_SQL_DELETE = 'pre.sql.delete';
    const EVENT_SQL_DELETE = 'sql.delete';
    const EVENT_POST_SQL_DELETE = 'post.sql.delete';

    const EVENT_PRE_SQL_UPDATE = 'pre.sql.update';
    const EVENT_SQL_UPDATE = 'sql.update';
    const EVENT_POST_SQL_UPDATE = 'post.sql.update';

    const EVENT_PRE_SQL_PATCH = 'pre.sql.patch';
    const EVENT_SQL_PATCH = 'sql.patch';
    const EVENT_POST_SQL_PATCH = 'post.sql.patch';

    const EVENT_PRE_SQL_INSERT = 'pre.sql.insert';
    const EVENT_SQL_INSERT = 'sql.insert';
    const EVENT_POST_SQL_INSERT = 'post.sql.insert';

    /**
     * @param \Zend\EventManager\EventManagerInterface $events
     * @param int                                      $priority
     *
     */
    public function attach(EventManagerInterface $events, $priority = 1000)
    {

        $this->listeners[] = $events->attach(SqlActuatorMapper::EVENT_MAPPER_FETCH, [$this, 'onMapperFetch'],
                                             $priority);
        $this->listeners[] = $events->attach(SqlActuatorMapper::EVENT_MAPPER_FETCH_ALL, [$this, 'onMapperFetchAll'],
                                             $priority);
        $this->listeners[] = $events->attach(SqlActuatorMapper::EVENT_MAPPER_CREATE, [$this, 'onMapperCreate'],
                                             $priority);
        $this->listeners[] = $events->attach(SqlActuatorMapper::EVENT_MAPPER_UPDATE, [$this, 'onMapperUpdate'],
                                             $priority);
        $this->listeners[] = $events->attach(SqlActuatorMapper::EVENT_MAPPER_PATCH, [$this, 'onMapperPatch'],
                                             $priority);
        $this->listeners[] = $events->attach(SqlActuatorMapper::EVENT_MAPPER_DELETE, [$this, 'onMapperDelete'],
                                             $priority);

    }


    /**
     *
     * @param \MessageExchangeEventManager\Event\EventInterface $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     */
    public function onMapperFetch(EventInterface $e)
    {
        $this->getEvent($e)->getRequest()->getParameters()->set('mapper_action', SqlActuatorMapper::EVENT_MAPPER_FETCH);

        $this->triggerEvent(self::EVENT_PRE_SQL_CONSTRAINT_WHERE, $this->getEvent($e));
        $response = $this->runEvent($this->getEvent($e), self::EVENT_PRE_SQL_SELECT, self::EVENT_SQL_SELECT,
                                    self::EVENT_POST_SQL_SELECT);

        return $response;
    }

    /**
     * @param \MessageExchangeEventManager\Event\EventInterface $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     */
    public function onMapperFetchAll(EventInterface $e)
    {
        $this->getEvent($e)->getRequest()->getParameters()->set('mapper_action',
                                                                SqlActuatorMapper::EVENT_MAPPER_FETCH_ALL);
        $response = $this->runEvent($this->getEvent($e), self::EVENT_PRE_SQL_SELECT, self::EVENT_SQL_SELECT,
                                    self::EVENT_POST_SQL_SELECT);
        return $response;
    }

    /**
     * @param \MessageExchangeEventManager\Event\EventInterface $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     */
    public function onMapperCreate(EventInterface $e)
    {
        $this->getEvent($e)->getRequest()->getParameters()->set('mapper_action',
                                                                SqlActuatorMapper::EVENT_MAPPER_CREATE);
        $response = $this->runEvent($this->getEvent($e), self::EVENT_PRE_SQL_INSERT, self::EVENT_SQL_INSERT,
                                    self::EVENT_POST_SQL_INSERT);
        return $response;
    }

    /**
     * @param \MessageExchangeEventManager\Event\EventInterface $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     */
    public function onMapperUpdate(EventInterface $e)
    {
        $this->getEvent($e)->getRequest()->getParameters()->set('mapper_action',
                                                                SqlActuatorMapper::EVENT_MAPPER_UPDATE);
        $this->triggerEvent(self::EVENT_PRE_SQL_CONSTRAINT_WHERE, $this->getEvent($e));

        $response = $this->runEvent($this->getEvent($e), self::EVENT_PRE_SQL_UPDATE, self::EVENT_SQL_UPDATE,
                                    self::EVENT_POST_SQL_UPDATE);
        return $response;
    }


    /**
     * @param \MessageExchangeEventManager\Event\EventInterface $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     */
    public function onMapperPatch(EventInterface $e)
    {
        $this->getEvent($e)->getRequest()->getParameters()->set('mapper_action', SqlActuatorMapper::EVENT_MAPPER_PATCH);
        $this->triggerEvent(self::EVENT_PRE_SQL_CONSTRAINT_WHERE, $this->getEvent($e));

        $response = $this->runEvent($this->getEvent($e), self::EVENT_PRE_SQL_PATCH, self::EVENT_SQL_PATCH,
                                    self::EVENT_POST_SQL_PATCH);
        return $response;
    }

    /**
     * @param \MessageExchangeEventManager\Event\EventInterface $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     */
    public function onMapperDelete(EventInterface $e)
    {
        $this->getEvent($e)->getRequest()->getParameters()->set('mapper_action',
                                                                SqlActuatorMapper::EVENT_MAPPER_DELETE);
        $this->triggerEvent(self::EVENT_PRE_SQL_CONSTRAINT_WHERE, $this->getEvent($e));

        $response = $this->runEvent($this->getEvent($e), self::EVENT_PRE_SQL_DELETE, self::EVENT_SQL_DELETE,
                                    self::EVENT_POST_SQL_DELETE);
        return $response;
    }

    /**
     * @param \MessageExchangeEventManager\Event\EventInterface $e
     *
     * @return \MessageExchangeEventManager\Event\Event
     */
    private function getEvent(EventInterface $e)
    {
        $event = new Event();
        $event->setRequest($e->getRequest());
        $event->setResponse($e->getResponse());
        $event->getTarget($this);
        return $event;
    }

}