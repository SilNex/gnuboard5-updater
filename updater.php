<?php
require(__DIR__ . '/class.php');

$updater = new Updater();
$argv[1] = isset($argv[1]) ? $argv[1] : null;
$parser = new SIRParser();
$parser->parseVersionList();
switch ($argv[1]) {
    case 'current':
        echo $parser->getCurrent()->version . PHP_EOL;
        break;
    case 'next':
        echo $parser->getNext()->version . PHP_EOL;
        break;
    case 'latest':
        echo $parser->getLatest()->version . PHP_EOL;
        break;
    case 'update':
        $force = $argv[2] === '--forece' ? true : false;
        if ($updater->update($force)) {
            echo '패치가 완료되었습니다.';
        } else {
            echo '패치할 파일에 변경사항이 있습니다.';
            var_dump($updater->diffFiles);
        }
        break;

    case 'restore':
        $updater->restore();
        break;

    case 'backup':
        $updater->backup();
        break;

    case 'diff':
        if (count($updater->diffFiles) > 0) {
            echo "오리지널 버전과 다른파일 목록 입니다.";
            print_r($updater->diffFiles);
        } else {
            echo "오리지널 버전과 다른파일이 없습니다.";
        }
        break;

    case 'clear':
        $withBackup = $argv[2] === '--backup' ? true : false;
        $updater->removePatchFiles($withBackup);
        break;

    default:
        $cmd = "php {$argv[0]}";
        echo "$cmd current\t: 현재 버전을 출력합니다.\n";
        echo "$cmd next\t: 다음 버전을 출력합니다.\n";
        echo "$cmd latest\t: 최신 버전을 출력합니다.\n";
        echo "$cmd update\t: 그누보드를 다음 버전으로 패치를 진행합니다.\n\t\t--force : 변경 파일이 있어도 강제로 덮어 씌웁니다.\n";
        echo "$cmd restore\t: 패치전 백업버전으로 되돌립니다.\n";
        echo "$cmd backup\t: 다음버전에서 수정되는 파일들을 백업합니다.\n";
        echo "$cmd diff\t: 현재 버전의 오리지널 파일과 다른파일 목록을 출력합니다.\n";
        echo "$cmd clear\t: 패치, 오리지널 파일을 삭제합니다.\n\t\t--backup : 백업파일도 함께 삭제합니다.\n";
        break;
}
