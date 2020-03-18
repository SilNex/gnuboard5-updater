<?php

namespace silnex\SIRUpdater;

use Exception;
use silnex\Util\Curl;

class Updater
{
    protected $versionManager;
    protected $publicPath, $basePath, $backupPath;
    protected $current, $next, $previous;

    public function __construct(VersionManager $versionManager, array $pathOption = [])
    {
        $this->versionManager = $versionManager;
        $this->publicPath = $versionManager->getPublicPath();
        $this->current = $this->versionManager->current();
        $this->next = $this->versionManager->next();
        // $this->previous = $this->versionManager->previous();
        $this->setPathInfo($pathOption);
    }

    protected function setPathInfo(array $pathOption = [])
    {
        $this->basePath = (isset($pathOption['base']) ? $pathOption['base'] : ($this->publicPath . '..' . DIRECTORY_SEPARATOR));
        $this->backupPath = $this->basePath . (isset($pathOption['backup']) ? $pathOption['backup'] : 'backup');
    }

    protected function downloadPatch(array $data)
    {
        $downloadLink = $data['detail']['patch'];
        $fileName = $data['version'] . '.patch.tar.gz';
        $storePath = $this->patchPath . str_replace('.', '_', $data['version']) . DIRECTORY_SEPARATOR . 'patch';
        $this->download($downloadLink, $fileName, $storePath);
        return $storePath . DIRECTORY_SEPARATOR . $fileName;
    }

    protected function downloadFull(array $data)
    {
        $downloadLink = $data['detail']['full'];
        $fileName = $data['version'] . '.tar.gz';
        $storePath = $this->fullPath . str_replace('.', '_', $data['version']) . DIRECTORY_SEPARATOR . 'full';
        $this->download($downloadLink, $fileName, $storePath);
        return $storePath . DIRECTORY_SEPARATOR . $fileName;
    }
    
    public function downloadNext()
    {
        return $this->downloadPatch($this->next);
    }

    public function downloadCurrent()
    {
        return $this->downloadFull($this->current);
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
        if (!file_put_contents($storePath . '/' . $fileName, $data)) {
            throw new Exception("{$filePath}에 저장을 실패했습니다");
        }
    }
}
