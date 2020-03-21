<?php

use silnex\SIRUpdater\Updater;

$updater = new Updater(__DIR__ . '/../../html');
$updater->update(true);
// $updater->restore();