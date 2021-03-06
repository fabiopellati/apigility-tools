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

namespace ApigilityTools\Rest\Entity\Listener;

use ApigilityTools\Rest\Entity\EventAwareEntity;
use MessageExchangeEventManager\Event\Event;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\InputFilter\InputFilter;

class InputFilterListenerAggregate
    extends AbstractListenerAggregate
{

    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     *
     * @param EventManagerInterface $events
     * @param int                   $priority
     *
     * @return void
     */
    public function attach(EventManagerInterface $events, $priority = 100)
    {

        $this->listeners[] = $events->attach(EventAwareEntity::EVENT_GET_ARRAY_COPY, [$this, 'onGetArrayCopy'],
                                             $priority);

    }

    /**
     *
     * @param Event $e
     *
     * @return \MessageExchangeEventManager\Response\ResponseInterface
     * @throws \MessageExchangeEventManager\Exception\InvalidParamException
     * @throws \MessageExchangeEventManager\Exception\ListenerRequirementException
     */
    public function onGetArrayCopy(Event $e)
    {
        $request = $e->getRequest();
        $response = $e->getResponse();
        $arrayCopy = $request->getParameters()->get('arrayCopy');
        if (is_array($arrayCopy)) {
            $inputFilterSpec = $request->getParameters()->get('inputFilterSpec');
            $inputFilter = new InputFilter();
            foreach ($inputFilterSpec as $input) {
                $inputFilter->add($input);
            }
            $inputFilter->setData($arrayCopy);
            foreach ($inputFilter->getValues() as $key => $value) {
                $arrayCopy[$key] = $value;
            }
            $request->getParameters()->set('arrayCopy', $arrayCopy);
            $response->setContent($arrayCopy);
        }

        return $response;

    }

}
