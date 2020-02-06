<?php
require(__DIR__ . '/interface.php');
require(__DIR__ . '/class.php');

// $t = new SIRParser();

// $t->parseVersionList();
// var_dump($t->getNext()
//     ->patchDownload()
//     ->extractPatchFile());

$up = new Updater();
var_dump($up->getPatchFileList());
