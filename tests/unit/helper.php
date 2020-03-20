<?php

use silnex\Util\Helper;

$files  = Helper::scanFiles(__DIR__ . '/../../5_4_1_5/patch');
var_dump($files);