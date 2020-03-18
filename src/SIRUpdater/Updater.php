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

    public function download(string $downloadLink, string $fileName, string $storePath = '/tmp')
    {
        if (!is_dir($storePath)) {
            mkdir($storePath, 0777, true);
        }

        $filePath = $storePath . '/' . $fileName;
        if (file_exists($filePath)) {
            throw new Exception("이미 파일이 존재합니다.");
        }

        $curl = new Curl;
        $data = $curl->get($downloadLink);
        file_put_contents($storePath . '/' . $fileName, $data);
    }
}
