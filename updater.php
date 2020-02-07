<?php
require(__DIR__ . '/interface.php');
require(__DIR__ . '/class.php');

$updater = new Updater();

// if (empty($updater->diffOriginUserFiles())) {
//     $updater->patch();
// } else {
//     var_dump($updater->restore());
// }
