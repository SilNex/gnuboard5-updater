<?php

use silnex\SIRUpdater\GnuboardSIRParserFactory;
use silnex\SIRUpdater\SIRParser;

$parserFactory = new GnuboardSIRParserFactory(); 
$parser = new SIRParser($parserFactory);
var_dump($parser->parsePostAttachFiles('5.4.1.0'));
