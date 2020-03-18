<?php

use silnex\SIRUpdater\GnuboardParserFactory;
use silnex\SIRUpdater\Parser;
use silnex\SIRUpdater\VersionManager;

try {
    $parser = new Parser(new GnuboardParserFactory());
    $vm = new VersionManager(__DIR__ . '/../../html/', $parser);
    var_dump($vm->current());
    var_dump($vm->next());
    var_dump($vm->previous());
} catch (Exception $e) {
    echo $e->getMessage();
}
