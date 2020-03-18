<?php

use silnex\SIRUpdater\VersionManager;

try {
    $vm = new VersionManager(__DIR__ . '/../../html/');
    echo $vm->current();
    echo $vm->next();
    echo $vm->previous();
} catch (Exception $e) {
    echo $e->getMessage();
}
