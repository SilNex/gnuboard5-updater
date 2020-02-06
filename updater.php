<?php
require (__DIR__ . '/interface.php');
require (__DIR__ . '/class.php');

$t = new SIRParser();

$version = $t->parseVersionList();
var_dump($version->getLatest());
