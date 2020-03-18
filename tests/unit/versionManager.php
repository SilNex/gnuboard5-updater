<?php

use silnex\SIRUpdater\GnuboardParserFactory;
use silnex\SIRUpdater\Parser;
use silnex\SIRUpdater\VersionManager;

try {
    $parser = new Parser(new GnuboardParserFactory());
    $vm = new VersionManager(__DIR__ . '/../../html/', $parser);
    echo $vm->current();
    echo $vm->next();
    echo $vm->previous();
} catch (Exception $e) {
    echo $e->getMessage();
}
