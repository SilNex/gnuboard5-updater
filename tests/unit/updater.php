<?php

use silnex\SIRUpdater\GnuboardSIRParserFactory;
use silnex\SIRUpdater\SIRParser;
use silnex\SIRUpdater\Updater;
use silnex\SIRUpdater\VersionManager;

$gnuFactory = new GnuboardSIRParserFactory();
$parser = new SIRParser($gnuFactory);
$vm = new VersionManager(__DIR__ . '/../../html', $parser);
$updater = new Updater($vm);
$updater->update(true);
// $updater->restore();