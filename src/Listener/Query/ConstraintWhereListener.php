<?php
/**
 * lo scopo di questo listener è quello di disaccoppiare la logica di filtraggio dell'id
 * per SELECT, UPDATE, DELETE
 *
 * per consentire di manipolare l'id filtrato prima dell'esecuzione della query nel caso ad esempio delle chiavi
 * composite
 *
 *
 */

namespace ApigilityTools\Listener\Query;

use ApigilityTools\Listener\Sql\SqlActuatorListenerInterface;
use MessageExchangeEventManager\Event\Event;
use MessageExchangeEventManager\EventManagerAwareTrait;
use MessageExchangeEventManager\Exception\ListenerRequirementException;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;

class ConstraintWhereListener
    extends AbstractListenerAggregate
{

    use EventManagerAwareTrait;


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
    public function attach(EventManagerInterface $events, $priority = 100)
    {

        $this->listeners[] = $events->attach(SqlActuatorListenerInterface::EVENT_PRE_SQL_CONSTRAINT_WHERE,
                                             [$this, 'onAttachSqlConstraintWhere'], $priority);
    }


    /**
     * l'attach è differito
     *
     * @param \MessageExchangeEventManager\Event\Event $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     */
    public function onAttachSqlConstraintWhere(Event $e)
    {
        $this->getEventManager()->attach(SqlActuatorListenerInterface::EVENT_SQL_SELECT,
                                         [$this, 'onSqlConstraintWhere'], 100)
        ;
        $this->getEventManager()->attach(SqlActuatorListenerInterface::EVENT_SQL_UPDATE,
                                         [$this, 'onSqlConstraintWhere'], 100)
        ;
        $this->getEventManager()->attach(SqlActuatorListenerInterface::EVENT_SQL_PATCH, [$this, 'onSqlConstraintWhere'],
                                         100)
        ;
        $this->getEventManager()->attach(SqlActuatorListenerInterface::EVENT_SQL_DELETE,
                                         [$this, 'onSqlConstraintWhere'], 100)
        ;

        return $e->getResponse();
    }

    /**
     * è un validatore di sicurezza:
     * verifica che sia stato iniettato un listener che si proclama aggiungendo un parametro hasCostraintWhere
     * che ha come responsabilità quella di accodare un adeguato where di filtraggio.
     * la logica è quella di minimizzare il rischio di eseguire query di modifica senza filtri che possano modificare
     * tutte le righe della collection in oggetto
     *
     * @param \MessageExchangeEventManager\Event\Event $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     */
    public function onSqlConstraintWhere(Event $e)
    {
        $request = $e->getRequest();
        $response = $e->getResponse();
        try {
//            print_r([__METHOD__ => '001']);
//            exit;

            $hasConstraint = $request->getParameters()->get('hasConstraintWhere');
            if (empty($hasConstraint) || !$hasConstraint) {
                throw new ListenerRequirementException('il metodo richiede un listener obbligatorio di tipo ConstraintWhere',
                                                       500);
            }
        } catch (\Exception $error) {
            $response->setError($error->getMessage(), $error->getCode());
            $e->stopPropagation();
        }

        return $response;
    }


}
