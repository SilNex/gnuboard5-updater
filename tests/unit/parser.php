<?php

require_once __DIR__ . '/../bootstrap.php';

use silnex\GnuboardUpdater\SIRParser;

$parser = new SIRParser;
var_dump($parser->getPostList());
