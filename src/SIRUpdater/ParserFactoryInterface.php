<?php

namespace silnex\SIRUpdater;

interface SIRParserFactoryInterface
{
    public function getUrl();
    public function getPostListPattern();
    public function getPostAttatchPattern();
}
