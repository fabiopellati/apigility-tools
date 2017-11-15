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
interface MapperUpdateAwareInterface
{
    const EVENT_MAPPER_PRE_UPDATE = 'mapper.update.pre';
    const EVENT_MAPPER_UPDATE = 'mapper.update';
    const EVENT_MAPPER_POST_UPDATE = 'mapper.update.post';

    public function update($id, $data);
}
