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

namespace ApigilityTools\Mapper;
interface MapperCreateAwareInterface
{
    const EVENT_MAPPER_PRE_CREATE = 'mapper.create.pre';
    const EVENT_MAPPER_CREATE = 'mapper.create';
    const EVENT_MAPPER_POST_CREATE = 'mapper.create.post';

    public function create($data);

}
