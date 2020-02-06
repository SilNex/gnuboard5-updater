<?php
require (__DIR__ . '/interface.php');
require (__DIR__ . '/class.php');

$t = new SIRParser();

$t->parseVersionList();
var_dump($t->getNext()->patchDownload()->extract());
