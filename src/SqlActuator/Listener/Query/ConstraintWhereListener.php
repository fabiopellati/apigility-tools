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

use ApigilityTools\SqlActuator\Listener\SqlActuatorListenerInterface;
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
    public function attach(EventManagerInterface $events, $priority = 250)
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
                                         [$this, 'onSqlConstraintWhere'], 250)
        ;
        $this->getEventManager()->attach(SqlActuatorListenerInterface::EVENT_SQL_UPDATE,
                                         [$this, 'onSqlConstraintWhere'], 250)
        ;
        $this->getEventManager()->attach(SqlActuatorListenerInterface::EVENT_SQL_PATCH, [$this, 'onSqlConstraintWhere'],
                                         250)
        ;
        $this->getEventManager()->attach(SqlActuatorListenerInterface::EVENT_SQL_DELETE,
                                         [$this, 'onSqlConstraintWhere'], 250)
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
            $hasConstraint = $request->getParameters()->get('hasConstraintWhere');
            if (empty($hasConstraint) || !$hasConstraint) {
                throw new ListenerRequirementException('il metodo richiede un listener obbligatorio di tipo ConstraintWhere',
                                                       500);
            }
        } catch (\Exception $error) {
            $response->setContent($error);
            $e->stopPropagation();
        }

        return $response;
    }

}
