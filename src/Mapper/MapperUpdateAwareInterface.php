<?php

namespace ApigilityTools\Mapper;

interface MapperUpdateAwareInterface
{
    const EVENT_MAPPER_PRE_UPDATE = 'mapper.update.pre';
    const EVENT_MAPPER_UPDATE = 'mapper.update';
    const EVENT_MAPPER_POST_UPDATE = 'mapper.update.post';

    public function update($id, $data);
}
