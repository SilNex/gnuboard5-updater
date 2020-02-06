<?php
require(__DIR__ . '/interface.php');
require(__DIR__ . '/class.php');

$t = new SIRParser();

// $t->parseVersionList();

// $t->getNext()
//     ->patchDownload()
//     ->extractPatchFile();

// $t->getCurrent()
//     ->fullVerDownload()
//     ->extractFullFile();

$up = new Updater();
var_dump($up->getUserFileList());
