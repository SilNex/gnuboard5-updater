<?php
require(__DIR__ . '/class.php');

$updater = new Updater();
$argv[1] = isset($argv[1]) ? $argv[1] : null;
switch ($argv[1]) {
    case 'update':
        if ($updater->update()) {
            echo '패치가 완료되었습니다.';
        } else {
            echo '패치할 파일에 변경사항이 있습니다.';
            var_dump($updater->diffFiles);
        }
        break;

    default:
        $cmd = "php {$argv[0]}";
        echo "$cmd update : 그누보드를 다음 버전으로 패치를 진행합니다.\n\t\t--force : 변경 파일이 있어도 강제로 덮어 씌웁니다.\n";
        echo "$cmd restore : 패치전 백업버전으로 되돌립니다.\n";
        echo "$cmd backup : 다음버전에서 수정되는 파일들을 백업합니다.\n";
        echo "$cmd diff : 현재 버전의 오리지널 파일과 다른파일 목록을 출력합니다.\n";
        echo "$cmd clear : 패치, 오리지널 파일을 삭제합니다.\n\t\t--backup : 백업파일도 함께 삭제합니다.\n";
        break;
}


// $updater->restore();
// $updater->removePatchFiles();
