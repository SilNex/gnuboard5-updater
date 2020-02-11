<?php
require(__DIR__ . '/class.php');

$updater = new Updater();

if ($updater->patch()) {
    echo '패치가 완료되었습니다.';
} else {
    echo '패치할 파일에 변경사항이 있습니다.';
    var_dump($updater->diffFiles);
}
// $updater->restore();
// $updater->removePatchFiles();