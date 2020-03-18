<?php

use silnex\SIRUpdater\VersionManager;


try {
    $vm = new VersionManager(__DIR__ . '/../../html/');
    echo $vm->current();
} catch (Exception $e) {
    echo $e->getMessage();
}