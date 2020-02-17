<?php
define('__GNU_DIR__', __DIR__ . '/html/');
define('__PATCH_DIR__', __DIR__ . '/patch/');
define('__ORIGIN_DIR__', __DIR__ . '/origin/');
define('__BACKUP_DIR__', __DIR__ . '/backup/');

class Version extends SplDoublyLinkedList
{
    public function __construct($array = [])
    {
        foreach ($array as $key => $value) {
            $this->$key = $value;
        }
    }

    protected function getCurrentVersionString()
    {
        if (!file_exists(__GNU_DIR__ . '/config.php')) {
            throw new Exception("\nconfig.php 파일을 찾을 수 없습니다. \n그누보드 경로를 확인해주세요.\n현재 경로:" . __GNU_DIR__ . PHP_EOL);
        }
        $configString = file_get_contents(__GNU_DIR__ . '/config.php');
        preg_match('/\(\'G5_GNUBOARD_VER\',\s\'([0-9.]+)\'\)\;/', $configString, $match);
        return isset($match[1]) ? $match[1] : null;
    }

    public function findInfo($version)
    {
        for ($i = 0; $i < $this->count(); $i++) {
            if ($version === $this->offsetGet($i)->version) {
                return $this->offsetGet($i);
            }
        }
        return null;
    }

    public function getLatest()
    {
        return $this->bottom();
    }

    public function getNext()
    {
        $version = $this->getCurrentVersionString();
        if (isset($version)) {
            for ($i = ($this->count() - 1); $i > -1; $i--) {
                if ($version < $this->offsetGet($i)->version) {
                    return $this->offsetGet($i);
                }
            }
        } else {
            return null;
        }
    }

    public function getCurrent()
    {
        $version = $this->getCurrentVersionString();
        if (isset($version)) {
            return $this->findInfo($version);
        } else {
            return null;
        }
    }
}

class SIRParser extends Version
{
    protected $sirBoardPattern = '/<a\shref=\"(.*)\"\sclass="title_link">\s+\[?(보안패치|정식버전|베타버전)?\]?\s?그누보드\s?(5\.4\.[0-9]\.[0-9])/';
    protected $sirAttachPattern = '/onclick=\"file_download\(\'(\/\/sir\.kr\/bbs\/download\.php.*)\',\s\'gnuboard5\.4\.[0-9]\.[0-9]\.patch\.tar\.gz\'\);\"/';
    protected $githubUriPattern = '/>(https:\/\/github\.com\/gnuboard\/gnuboard5\/commit\/[a-z0-9]+)</';

    /**
     * html페이지를 가져온다.
     * 
     * @return string
     */
    public function get($url, $param = [], &$file = null)
    {
        if (empty($param)) {
            $query = http_build_query($param);
            $url .= "?{$query}";
        }

        if (extension_loaded('curl')) {
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_USERAGENT, 'gnuboard-updater');
            if (isset($file)) {
                curl_setopt($ch, CURLOPT_FILE, $file);
            }
            $response = curl_exec($ch);

            curl_close($ch);

            if ($response !== false) {
                return $response;
            }
        } else {
            // create file code add
            // return file_get_contents($url);
            throw new Exception("php-curl이 없습니다.");
        }
    }

    /**
     * 다운로드 페이지에서 Version 클래스를 반환한다.
     * 
     * @return SIRParser
     */
    public function parseVersionList()
    {
        if ($this->isEmpty()) {
            do {
                $uri = "https://sir.kr/g5_pds";
                $page = 1;
                $response = $this->get($uri, ['page' => $page]);
                preg_match_all($this->sirBoardPattern, $response, $matches);
                for ($i = 0; $i < count($matches[0]); $i++) {
                    $this->push(new SIRParser([
                        'href' => "https:" . $matches[1][$i],
                        'info' => $matches[2][$i],
                        'version' => $matches[3][$i]
                    ]));
                }
                $page++;
            } while ($this->top()->version < '5.4.0.0');
        }
    }

    /**
     * 해당 버전에 대한 자세한 정보(download link, github link) 수집.
     * 
     * @return SIRParser
     */
    public function parseDetail()
    {
        if (in_array($this->info, ['베타버전'])) {
            throw new Exception("베타버전은 업데이트 되지 않습니다.");
        } else {
            $response = $this->get($this->href);
            if (preg_match($this->sirAttachPattern, $response, $match)) {
                $this->patchHref = 'https:' . str_replace('download', 'download2', html_entity_decode($match[1]));
                $this->originHref = str_replace('&no=1', '&no=0', $this->patchHref);
            } else {
                throw new Exception("패치파일을 찾을 수 없습니다.");
            }

            if (preg_match_all($this->githubUriPattern, $response, $matches)) {
                $this->githubLinks = $matches[1];
            }

            return $this;
        }
    }

    public function patchDownload()
    {
        if (!isset($this->patchHref)) {
            $this->parseDetail();
        }

        $this->patchTarFile = $this->download(
            $this->patchHref,
            __PATCH_DIR__,
            'gnuboard' . $this->version . '.patch.tar.gz'
        );

        $this->patchFile = new PharData($this->patchTarFile);

        return $this;
    }

    /**
     * 현재 버전의 패치파일을 다운로드합니다.
     * 
     * @return SIRParser
     */
    public function originVerDownload()
    {
        if (!isset($this->originHref)) {
            $this->parseDetail();
        }

        $this->originTarFile = $this->download(
            $this->originHref,
            __ORIGIN_DIR__,
            'gnuboard' . $this->version . '.tar.gz'
        );

        $this->originFile = new PharData($this->originTarFile);

        return $this;
    }

    /**
     * @param string $version
     * $version 버전의 풀버전을 다운로드합니다.
     * 
     * @return void
     */
    public function versionDownload($version)
    {
        $downloadFile = str_replace('.', '_', $version);
        $downloadPath = __DIR__ . '/' . $downloadFile;
        if (is_dir($downloadPath)) {
            throw new Exception("{$version} 버전의 폴더가 이미 존재합니다.\n");
        }
        if ($downloadVersion = $this->findInfo($version)) {
            $downloadVersion->parseDetail();
            $this->download($downloadVersion->originHref, $downloadPath, $downloadFile . '.tar.gz');
        } else {
            throw new Exception("{$version}을 찾을수 없습니다.\n베타버전과 5.4미만 버전은 지원되지 않습니다.\n");
        }
    }

    /**
     * @param string $path 저장 경로
     * @param string $url 다운로드 링크
     * @param string $fileName 파일명
     * $url의 파일을 $path에 $fileName으로 저장함
     * 
     * @return string 다운로드한 파일 경로
     */
    public function download($url, $path, $fileName)
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $filePath = $path . '/' . $fileName;
        $fp = fopen($filePath, 'w+');

        $this->get($url, [], $fp);
        fclose($fp);

        return $filePath;
    }

    /**
     * 현재 버전의 패치파일을 압축해제
     * skin, theme, mobile/skin을 삭제
     * 
     * @return string 압축해제 경로
     */
    public function extractPatchFile()
    {
        if (!isset($this->patchFile)) {
            throw new Exception("패치 파일이 없습니다.");
        }

        $path = $this->extract($this->patchFile, __PATCH_DIR__);

        // 스킨 파일 삭제
        self::rmrf($path . '/theme');
        self::rmrf($path . '/skin');
        self::rmrf($path . '/mobile/skin');

        // 압축 파일 삭제
        self::rmrf($this->patchTarFile);
        unset($this->patchFile);

        return $path;
    }

    /**
     * @param string $path 압축 해제 경로
     * 
     * @return $path 압축해제 경로
     */
    public function extractOriginFile()
    {
        if (!isset($this->originFile)) {
            throw new Exception("파일이 없습니다.");
        }

        $path = $this->extract($this->originFile, __ORIGIN_DIR__);

        // 압축 파일 삭제
        self::rmrf($this->originTarFile);
        unset($this->originFile);

        return $path;
    }

    public function extract(&$tarHeader, $path = null)
    {
        $tarHeader->extractTo($path, null, true);
        return $path;
    }

    /**
     * @param string $path 경로 안에 모든 모든 파일을 삭제한다.
     * 
     * @return void
     */
    static public function rmrf($path)
    {
        foreach (glob($path, GLOB_MARK | GLOB_BRACE) as $file) {
            if (is_dir($file)) {
                self::rmrf($file . '{,.}[!.,!..]*');
                rmdir($file);
            } else {
                unlink($file);
            }
        }
    }
}

class Updater
{
    public $patchPath = __PATCH_DIR__;
    public $userPath = __GNU_DIR__;
    public $originPath = __ORIGIN_DIR__;
    public $backupPath = __BACKUP_DIR__;

    public $baseFiles = [];
    public $patchFiles = [];
    public $userFiles = [];
    public $originFiles = [];
    public $backupFiles = [];

    public $diffFiles = [];

    public $parser;

    public function __construct()
    {
        if (!isset($this->parser)) {
            $this->parser = new SIRParser();
            $this->parser->parseVersionList();
        }
        if (!is_dir($this->patchPath) || !is_dir($this->originPath)) {
            if (!is_null($this->parser->getCurrent())) {
                $this->parser->getNext()->patchDownload()->extractPatchFile();
                $this->parser->getCurrent()->originVerDownload()->extractOriginFile();
            }
        }

        $this->getFileList($this->patchPath);
        $this->setDiffFiles();

        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0777, true);
        }
    }

    public function getFileList($path, &$files = [], $depth = 1)
    {
        foreach (glob($path) as $file) {
            if (is_dir($file)) {
                $this->getFileList("$file/*", $files, $depth++);
            } else {
                $filePath = str_replace($this->patchPath . '/', '', $file);
                $this->baseFiles[] = $filePath;
                $this->patchFiles[] = $this->patchPath . $filePath;
                $this->userFiles[] = $this->userPath . $filePath;
                $this->originFiles[] = $this->originPath . $filePath;
            }
        }
    }

    public function setDiffFiles()
    {
        for ($i = 0; $i < count($this->userFiles); $i++) {
            if (file_get_contents($this->userFiles[$i]) !== file_get_contents($this->originFiles[$i])) {
                $this->diffFiles[] = $this->userFiles[$i];
            }
        }
    }

    public function hasDiff()
    {
        return empty($this->diffFiles) ? false : true;
    }

    public function update($force = false)
    {
        if ($this->hasDiff() && !$force) {
            return false;
        } else {
            $this->backup();

            for ($i = 0; $i < count($this->userFiles); $i++) {
                copy($this->patchFiles[$i], $this->userFiles[$i]);
            }
            return true;
        }
    }

    public function backup()
    {
        foreach ($this->userFiles as $file) {
            $backupFilePath = str_replace('.', $this->backupPath, dirname($file));
            if (!is_dir($backupFilePath)) {
                mkdir($backupFilePath, 0777, true);
            }
            copy($file, $backupFilePath . '/' . basename($file));
        }
    }

    public function restore()
    {
        if (!is_dir($this->backupPath)) {
            throw new Exception("벡업파일이 존재하지 않습니다.");
        }
        for ($i = 0; $i < count($this->backupFiles); $i++) {
            copy($this->backupFiles[$i], $this->userFiles[$i]);
        }
    }

    public function removePatchFiles($withBackup = false)
    {
        SIRParser::rmrf($this->originPath);
        SIRParser::rmrf($this->patchPath);
        if ($withBackup) {
            SIRParser::rmrf($this->backupPath);
        }
    }
}


$updater = new Updater();
$argv[1] = isset($argv[1]) ? $argv[1] : null;
switch ($argv[1]) {
    case 'current':
        echo $updater->parser->getCurrent()->version . PHP_EOL;
        break;

    case 'next':
        echo $updater->parser->getNext()->version . PHP_EOL;
        break;

    case 'latest':
        echo $updater->parser->getLatest()->version . PHP_EOL;
        break;

    case 'update':
        if (isset($argv[2])) {
            $force = $argv[2] === '--forece' ? true : false;
        }
        if ($updater->update($force)) {
            echo '패치가 완료되었습니다.' . PHP_EOL;
            $updater->removePatchFiles();
        } else {
            echo '패치할 파일에 변경사항이 있습니다.' . PHP_EOL;
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
            echo "오리지널 버전과 다른파일 목록 입니다.\n";
            print_r($updater->diffFiles);
        } else {
            echo "오리지널 버전과 다른파일이 없습니다.\n";
        }
        break;

    case 'download':
        if (isset($argv[2])) {
            $updater->parser->versionDownload($argv[2]);
        } else {
            echo "버전 정보를 함께 입력하셔야 합니다.\n";
        }
        break;

    case 'clear':
        if (isset($argv[2])) {
            $withBackup = $argv[2] === '--backup' ? true : false;
        }
        $updater->removePatchFiles($withBackup);
        break;

    default:
        $cmd = "php {$argv[0]}";
        echo "$cmd current\t: 현재 버전을 출력합니다.\n";
        echo "$cmd next\t: 다음 버전을 출력합니다.\n";
        echo "$cmd latest\t: 최신 버전을 출력합니다.\n";
        echo "$cmd update [--force]\t: 그누보드를 다음 버전으로 패치를 진행합니다.[--force: 변경 파일이 있어도 강제로 덮어 씌웁니다.]\n";
        echo "$cmd restore\t: 패치전 백업버전으로 되돌립니다.\n";
        echo "$cmd backup\t: 다음버전에서 수정되는 파일들을 백업합니다.\n";
        echo "$cmd download {version} [--extract]\t: {version}의 풀버전 파일을 다운합니다. [--extract : 다운로드파일을 압축 해제 합니다.]\n";
        echo "$cmd diff\t: 현재 버전의 오리지널 파일과 다른파일 목록을 출력합니다.\n";
        echo "$cmd clear [--backup]\t: 패치, 오리지널 파일을 삭제합니다. [--backup: 백업파일도 함께 삭제합니다.]\n";
        break;
}
