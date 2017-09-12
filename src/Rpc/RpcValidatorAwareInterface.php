<?php
/**
 * Created by PhpStorm.
 * User: fabio
 * Date: 12/09/17
 * Time: 15.59
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