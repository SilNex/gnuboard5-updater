<?php

namespace silnex\SIRUpdater;

interface ParserFactoryInterface
{
    public function getUrl();
    public function getPostListPattern();
    public function getPostAttatchPattern();
}
