<?php
/**
 * Created by PhpStorm.
 * User: fabio
 * Date: 22/02/17
 * Time: 17.44
 */

namespace ApigilityTools\Listener;

use ApigilityTools\Exception\RuntimeException;
use ApigilityTools\Listener\Sql\SqlActuatorListener;
use MessageExchangeEventManager\Exception\ListenerRequirementException;
use Zend\Db\Metadata\Source\Factory;
use Zend\Db\Sql\AbstractPreparableSql;
use Zend\Db\Sql\Ddl\Column\Column;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;

class SoftDeleteListener extends AbstractListenerAggregate
{

    /**
     * @var \Zend\Cache\Storage\Adapter\AbstractAdapter
     */
    protected $cache;


    /**
     * @return \Zend\Cache\Storage\Adapter\AbstractAdapter
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param mixed $cache
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
    }

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
    public function attach(EventManagerInterface $events, $priority = 10000)
    {

        $this->listeners[] = $events->attach(SqlActuatorListener::EVENT_SQL_DELETE, [$this, 'onDelete'],
                                             $priority + 1000);
        $this->listeners[] = $events->attach(SqlActuatorListener::EVENT_PRE_SQL_SELECT, [$this, 'onSelect'], $priority);
    }

    /**
     * sull'evento select aggiunge sempre una condizione
     * where soft_delete=0
     *
     * @param \Zend\EventManager\Event $e
     */
    public function onSelect(Event $e)
    {
        $request = $e->getRequest();
        $response = $e->getResponse();

        try {
            $sql = $request->getParameters()->get('sql');
            $this->checkTable($sql);
            $query = $request->getParameters()->get('query');
            $this->validateQuery($query);
            $query->where(function (Where $where) {
                $where->and->NEST->equalTo('soft_delete', 0);
            });
        } catch (\Exception $error) {
            $response->setError($error->getMessage(), $error->getCode());
            $e->stopPropagation();
        }
        return $response;

    }

    /**
     *
     * su delete aggiorna il campo softdelet al valore 1
     * l'implementazione del listenere prevede anche che a valle l'esecuzione del delete venga skipapta
     *
     * @param \Zend\EventManager\Event $e
     *
     * @return int
     */
    public function onDelete(Event $e)
    {
        $response = $e->getResponse();
        try {
            $request = $e->getRequest();
            $mapper = $request->getParameters()->get('mapper');
            $id = $request->getParameters()->get('id');
            $sql = $request->getParameters()->get('sql');
            $this->checkTable($sql);
            $data = [];
            $data['soft_delete'] = '1';

            $result = $mapper->update($id, $data);
            $response->setContent($result > 0);
            $e->stopPropagation();

        } catch (\Exception $error) {
            $response->setError($error->getMessage(), $error->getCode());
            $e->stopPropagation();
        }
        return $response;

    }

    /**
     * @param  \Zend\Db\Sql\Sql $sql
     *
     * @return bool
     * @throws \ApigilityTools\Exception\RuntimeException
     */
    protected function checkTable(Sql $sql)
    {

        $tableIdentifier = $sql->getTable();

        $cache_key = "table_metadata_columns_{$tableIdentifier->getTable()}_{$tableIdentifier->getSchema()}";
        $columns = ($this->getCache()) ? $this->getCache()->getItem($cache_key) : null;

        if (!$columns) {
            $metadata = Factory::createSourceFromAdapter($sql->getAdapter());
            $metadata_table = $metadata->getTable($tableIdentifier->getTable(), $tableIdentifier->getSchema());
            $columns = $metadata_table->getColumns();
            if ($this->getCache()) {
                $this->getCache()->setItem($cache_key, $columns);
            }
        }

        /**
         * @var $column Column
         */
        foreach ($columns as $column) {
            if ($column->getName() === 'soft_delete') {
                return true;
            }
        }
        throw new RuntimeException('SoftDelete richiede un campo soft_delete. Aggiungere il campo alla tabella ' . $tableIdentifier->getTable());
    }

    /**
     * @param $query
     *
     * @throws \MessageExchangeEventManager\Exception\ListenerRequirementException
     */
    private function validateQuery($query)
    {
        if (empty($query) || !$query instanceof AbstractPreparableSql) {
            throw new ListenerRequirementException('parametro query non presente: possibile errore nella sequenza dei listener ',
                                                   500);
        }
    }
}