<?php

namespace silnex\SIRUpdater;

use Exception;
use silnex\Util\Curl;

class Updater
{
    protected $versionManager;
    protected $publicPath, $basePath, $backupPath, $patchPath, $fullPath;

    public function __construct(VersionManager $versionManager, array $pathOption = [])
    {
        $this->versionManager = $versionManager;
        $this->setPathInfo($pathOption);
    }

    public function setPathInfo(array $pathOption = [])
    {
        $base = $this->basePath;
        $this->backupPath = $base . (isset($pathOption['backup']) ? $pathOption['backup'] : 'backup');
        $this->patchPath = $base . (isset($pathOption['patch']) ? $pathOption['patch'] : 'patch');
        $this->fullPath = $base . (isset($pathOption['full']) ? $pathOption['full'] : 'full');
    }

    public function download(string $downloadLink, string $storePath)
    {
        $pathInfo = pathinfo($storePath);
        
        $dirName = $pathInfo['dirname'];
        $fileName = $pathInfo['basename'];

        if (!is_dir($dirName)) {
            mkdir($dirName, 0777, true);
        }

        if (file_exists($storePath)) {
            throw new Exception("이미 파일이 존재합니다.");
        }
    }
}
