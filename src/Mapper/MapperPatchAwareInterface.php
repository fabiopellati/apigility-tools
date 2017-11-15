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
interface MapperPatchAwareInterface
{
    const EVENT_MAPPER_PRE_PATCH = 'mapper.patch.pre';
    const EVENT_MAPPER_PATCH = 'mapper.patch';
    const EVENT_MAPPER_POST_PATCH = 'mapper.patch.post';

    public function patch($id, $data);

}
