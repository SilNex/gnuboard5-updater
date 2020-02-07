<?php
define('__PATCH_DIR__', __DIR__ . '/patch');
define('__FULL_DIR__', __DIR__ . '/full');

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
        $configString = file_get_contents(__DIR__ . '/config.php');
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

class SIRParser extends Version implements SIRParserInterface
{
    protected $sirBoardPattern = '/<a\shref=\"(.*)\"\sclass="title_link">\s+\[?(보안패치|정식버전|베타버전)?\]?\s?그누보드\s?(5\.4\.[0-9]\.[0-9])/';
    protected $sirAttachPattern = '/onclick=\"file_download\(\'(\/\/sir\.kr\/bbs\/download\.php.*)\',\s\'gnuboard5\.4\.[0-9]\.[0-9]\.patch\.tar\.gz\'\);\"/';
    protected $githubUriPattern = '/>(https:\/\/github\.com\/gnuboard\/gnuboard5\/commit\/[a-z0-9]+)</';

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

    public function parseDetail()
    {
        if (in_array($this->info, ['베타버전'])) {
            throw new Exception("베타버전은 업데이트 되지 않습니다.");
        } else {
            $response = $this->get($this->href);
            if (preg_match($this->sirAttachPattern, $response, $match)) {
                $this->patchHref = 'https:' . str_replace('download', 'download2', html_entity_decode($match[1]));
                $this->fullHref = str_replace('&no=1', '&no=0', $this->patchHref);
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

    public function fullVerDownload()
    {
        if (!isset($this->fullHref)) {
            $this->parseDetail();
        }

        $this->fullTarFile = $this->download(
            $this->fullHref,
            __FULL_DIR__,
            'gnuboard' . $this->version . '.tar.gz'
        );

        $this->fullFile = new PharData($this->fullTarFile);

        return $this;
    }

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

    public function extractFullFile()
    {
        if (!isset($this->fullFile)) {
            throw new Exception("파일이 없습니다.");
        }

        $path = $this->extract($this->fullFile, __FULL_DIR__);

        // 압축 파일 삭제
        self::rmrf($this->fullTarFile);
        unset($this->fullFile);

        return $path;
    }

    public function extract(&$tarHeader, $path = null)
    {
        $tarHeader->extractTo($path, null, true);
        return $path;
    }

    static public function rmrf($path)
    {
        foreach (glob($path) as $file) {
            if (is_dir($file)) {
                self::rmrf("$file/*");
                rmdir($file);
            } else {
                unlink($file);
            }
        }
    }
}

class Updater
{
    public function getFileList($path, &$files = [], $depth = 1, $removePath = __PATCH_DIR__)
    {
        foreach (glob($path) as $file) {
            if (is_dir($file)) {
                self::getFileList("$file/*", $files, $depth++);
            } else {
                $files[] = str_replace($removePath, '', $file);
            }
        }
        return $files;
    }

    public function getPatchFileList()
    {
        $files = [];
        foreach ($this->getFileList(__PATCH_DIR__) as $path) {
            $files[] = './patch' . $path;
        }
        return $files;
    }

    public function getFileListBase()
    {
        return $this->getFileList(__PATCH_DIR__);
    }

    public function getOriginFileList()
    {
        $files = [];
        foreach ($this->getFileListBase() as $path) {
            $files[] = './full' . $path;
        }
        return $files;
    }

    public function getUserFileList($path = null)
    {
        // path user파일 가져오기
        $files = [];
        foreach ($this->getFileListBase() as $path) {
            $files[] = '.' . $path;
        }
        return $files;
    }

    public function diffOriginUserFiles()
    {
        $userFiles = $this->getUserFileList();
        $originFiles = $this->getOriginFileList();
        $diff = [];
        for ($i = 0; $i < count($userFiles); $i++) {
            if (file_get_contents($userFiles[$i]) !== file_get_contents($originFiles[$i])) {
                echo "diff {$userFiles[$i]} <=> {$originFiles[$i]}" . PHP_EOL;
                $diff[] = $userFiles[$i];
            }
        }
        return $diff;
    }

    public function patch()
    {
        $userFiles = $this->getUserFileList();
        $patchFiles = $this->getPatchFileList();

        $this->backup($userFiles);

        for ($i = 0; $i < count($userFiles); $i++) {
            copy($patchFiles[$i], $userFiles[$i]);
        }
    }

    public function backup($files)
    {
        $backupPath = './backup';
        if (!is_dir('./backup')) {
            mkdir('./backup', 0777, true);
        }
        foreach ($files as $file) {
            $backupFilePath = str_replace('.', $backupPath, dirname($file));
            if (!is_dir($backupFilePath)) {
                mkdir($backupFilePath, 0777, true);
            }
            copy($file, $backupFilePath . '/' . basename($file));
        }
    }
    
    public function restore()
    {
        $backupPath = './backup';
        $backupFiles = $this->getFileList($backupPath);
        $userFiles = $this->getUserFileList($backupPath);
        
        for ($i=0; $i < count($backupFiles); $i++) { 
            copy($backupFiles[$i], $userFiles[$i]);
        }
    }
}
