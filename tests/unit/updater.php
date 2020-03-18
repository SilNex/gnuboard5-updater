<?php

use silnex\SIRUpdater\GnuboardParserFactory;
use silnex\SIRUpdater\Parser;
use silnex\SIRUpdater\Updater;
use silnex\SIRUpdater\VersionManager;

$gnuFactory = new GnuboardParserFactory();
$parser = new Parser($gnuFactory);
$vm = new VersionManager(__DIR__ . '/../../html/', $parser);
$updater = new Updater($vm);

$updater->download('https://sir.kr/bbs/download2.php?bo_table=g5_pds&wr_id=5024&no=1&page=1', 'test.patch.gz');