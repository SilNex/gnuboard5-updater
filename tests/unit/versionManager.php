<?php

use silnex\SIRUpdater\GnuboardSIRParserFactory;
use silnex\SIRUpdater\SIRParser;
use silnex\SIRUpdater\VersionManager;

try {
    $parser = new SIRParser(new GnuboardSIRParserFactory());
    $vm = new VersionManager(__DIR__ . '/../../html/', $parser);
    var_dump($vm->current());
    var_dump($vm->next());
    var_dump($vm->previous());
} catch (Exception $e) {
    echo $e->getMessage();
}
