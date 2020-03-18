<?php

use silnex\SIRUpdater\GnuboardParserFactory;
use silnex\SIRUpdater\Parser;

$parserFactory = new GnuboardParserFactory(); 
$parser = new Parser($parserFactory);
var_dump($parser->parsePostAttachFiles('5.4.1.0'));
