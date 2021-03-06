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

namespace ApigilityTools\Rpc;

use Zend\Validator\ValidatorChain;

interface RpcValidatorAwareInterface
{
    /**
     * @return \Zend\Validator\ValidatorChain
     */
    function getValidator();

    /**
     * @param \Zend\Validator\ValidatorChain $validator
     *
     * @return mixed
     */
    function setValidator(ValidatorChain $validator);
}