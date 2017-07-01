<?php

namespace ApigilityTools\Mapper;

interface FetchAwareInterface
{
    public function fetch($id);

    public function fetchAll();

}
