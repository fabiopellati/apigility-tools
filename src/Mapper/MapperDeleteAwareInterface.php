<?php

namespace ApigilityTools\Mapper;

interface MapperDeleteAwareInterface
{
    const EVENT_MAPPER_PRE_DELETE = 'mapper.delete.pre';
    const EVENT_MAPPER_DELETE = 'mapper.delete';
    const EVENT_MAPPER_POST_DELETE = 'mapper.delete.post';

    public function delete($id);
}
