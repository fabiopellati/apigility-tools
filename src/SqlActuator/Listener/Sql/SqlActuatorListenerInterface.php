<?php
/**
 * Created by PhpStorm.
 * User: fabio
 * Date: 19/04/17
 * Time: 11.43
 */

namespace ApigilityTools\SqlActuator\Listener\Sql;

use MessageExchangeEventManager\Event\EventInterface;

interface SqlActuatorListenerInterface
{

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
     * @param \MessageExchangeEventManager\Event\EventInterface $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     */
    public function onMapperEvent(EventInterface $e);

}