<?php

namespace silnex\SIRUpdater;

use Exception;
use silnex\Util\Downloader;

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

    /**
     * @param string $type = 'full'|'patch'
     * @param array $data = ['detail' => ['full' => href, 'patch' => href]]
     * 
     * @return string $storeFilePath
     */
    protected function downloadCode(string $type, array $data)
    {
        if (in_array($type, ['full', 'patch'])) {
            $downloadLink = $data['detail'][$type];

            $fileName = $data['version'] . ($type === 'full' ?: '.patch') . '.tar.gz';

            $path = $type . 'path';
            $storePath = $this->$path . str_replace('.', '_', $data['version']) . DIRECTORY_SEPARATOR . $type;

            $this->download($downloadLink, $fileName, $storePath);

            return $storePath . DIRECTORY_SEPARATOR . $fileName;
        } else {
            throw new Exception("잘못된 타입을 요청 하였습니다\n허용된 타입: full, patch\n요청된 타입:{$type}\n");
        }
    }

    public function downloadNext()
    {
        return $this->downloadCode('patch', $this->next);
    }

    public function downloadCurrent()
    {
        return $this->downloadCode('full', $this->current);
    }

    public function download(string $downloadLink, string $fileName, string $storePath = '/tmp')
    {
        Downloader::download($downloadLink, $fileName, $storePath);
    }
}
