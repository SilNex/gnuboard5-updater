<?php
require (__DIR__ . '/interface.php');
require (__DIR__ . '/class.php');

$t = new SIRParser();

$t->parseVersionList();
echo $t->getLatest()->parseDetail();
