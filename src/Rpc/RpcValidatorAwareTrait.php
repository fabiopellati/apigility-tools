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

trait RpcValidatorAwareTrait
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
        if (!$this->validator) {
            $this->setValidator(new ValidatorChain());
        }

        return $this->validator;
    }

    /**
     * @param \Zend\Validator\ValidatorChain $validator
     */
    public function setValidator(ValidatorChain $validator)
    {
        $this->validator = $validator;
    }

}