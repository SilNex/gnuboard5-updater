<?php
include('updaterConfig.php');

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
        $configString = file_get_contents(__GNU_DIR__ . '/config.php');
        preg_match('/\(\'G5_GNUBOARD_VER\',\s\'([0-9.]+)\'\)\;/', $configString, $match);
        return $match[1];
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
        for ($i = ($this->count() - 1); $i > -1; $i--) {
            if ($version < $this->offsetGet($i)->version) {
                return $this->offsetGet($i);
            }
        }
        return null;
    }

    public function getCurrent()
    {
        return $this->findInfo($this->getCurrentVersionString());
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
        foreach (glob($path, GLOB_MARK|GLOB_BRACE) as $file) {
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

    public function __construct()
    {
        if (!is_dir($this->patchPath) || !is_dir($this->originPath)) {
            $parser = new SIRParser();
            $parser->parseVersionList();
            $parser->getNext()->patchDownload()->extractPatchFile();
            $parser->getCurrent()->originVerDownload()->extractOriginFile();
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
                echo "diff {$this->userFiles[$i]} <=> {$this->originFiles[$i]}" . PHP_EOL;
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
        if (is_dir($this->backupPath)) {
            throw new Exception("벡업파일이 이미 백업파일이 존재합니다.");
        }
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
