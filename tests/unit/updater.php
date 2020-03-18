<?php

use silnex\SIRUpdater\GnuboardParserFactory;
use silnex\SIRUpdater\Parser;
use silnex\SIRUpdater\Updater;
use silnex\SIRUpdater\VersionManager;

$gnuFactory = new GnuboardParserFactory();
$parser = new Parser($gnuFactory);
$vm = new VersionManager(__DIR__ . '/../../html', $parser);
$updater = new Updater($vm);

try {
    $updater->readyForUpdate();
} catch (Exception $e) {
    echo $e->getMessage();
}