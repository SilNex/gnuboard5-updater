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

$updater = new Updater();
if (empty($updater->diffOriginUserFiles())) {
    $updater->patch();
} else {
    var_dump($updater->restore());
}
