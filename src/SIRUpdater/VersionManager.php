<?php

namespace silnex\SIRUpdater;

use Exception;

interface VersionManagerInterface
{
    public function next();
    public function current();
    public function previous();
}

class VersionManager implements VersionManagerInterface
{
    protected $versionList = [];
    protected $paser;
    protected $basePath, $publicPath, $backupPath, $patchPath, $fullPath;

    public function __construct(string $publicPath)
    {
        $this->publicPath = $publicPath . DIRECTORY_SEPARATOR;
        $this->basePath = $publicPath . '..' . DIRECTORY_SEPARATOR;
        $this->backupPath = $this->basePath . 'backup' . DIRECTORY_SEPARATOR;
        $this->patchPath = $this->basePath . 'patch' . DIRECTORY_SEPARATOR;
        $this->fullPath = $this->basePath . 'full' . DIRECTORY_SEPARATOR;

        if (!is_dir($publicPath)) {
            throw new Exception("경로가 잘못되었습니다.");
        } elseif (file_exists($publicPath . 'config.php')) {
            throw new Exception("config.php 파일을 찾을 수 없습니다.");
        }

        $this->parser = new Parser;
        $this->versionList = $this->parser->getPostList();
    }
}
