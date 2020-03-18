<?php

use silnex\GnuboardUpdater\SIRParser;

$parser = new SIRParser;
var_dump($parser->getPostAttachFiles('5.4.1.0'));
