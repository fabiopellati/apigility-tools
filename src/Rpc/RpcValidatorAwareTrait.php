<?php
/**
 * Created by PhpStorm.
 * User: fabio
 * Date: 12/09/17
 * Time: 16.00
 */

namespace ApigilityTools\Rpc;
class RpcValidatorAwareTrait
{
    /**
     * @var \Zend\Validator\ValidatorChain
     */
    protected $validator;

    /**
     * @return \Zend\Validator\ValidatorChain
     */
    public function getValidator()
    {
        return $this->validator;
    }

    /**
     * @param \Zend\Validator\ValidatorChain $validator
     */
    public function setValidator($validator)
    {
        $this->validator = $validator;
    }


}