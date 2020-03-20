<?php

use silnex\SIRUpdater\GnuboardParserFactory;
use silnex\SIRUpdater\Parser;
use silnex\SIRUpdater\Updater;
use silnex\SIRUpdater\VersionManager;

$gnuFactory = new GnuboardParserFactory();
$parser = new Parser($gnuFactory);
$vm = new VersionManager(__DIR__ . '/../../html', $parser);
$updater = new Updater($vm);

var_dump($updater->getVersionPath('next', 'full'), $updater->getVersionPath('next', 'full'));