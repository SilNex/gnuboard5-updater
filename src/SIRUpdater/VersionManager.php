<?php

namespace silnex\SIRUpdater;

use Exception;
use InvalidArgumentException;

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
    protected $publicPath;
    protected $current;

    public function __construct(string $publicPath, Parser $parser)
    {
        $this->publicPath = $publicPath . DIRECTORY_SEPARATOR;

        if (!is_dir($this->publicPath)) {
            throw new Exception("{$this->publicPath}\n경로가 잘못되었습니다.\n");
        } elseif (!file_exists($this->publicPath . 'config.php')) {
            throw new Exception("{$this->publicPath}\nconfig.php 파일을 찾을 수 없습니다.\n");
        }

        $this->current = $this->getCurrentVersion();
        $this->parser = $parser;
        $this->versionList = $this->parser->getPostList();
        $this->next = $this->getNextVersion();
        $this->previous = $this->getpreviousVersion();
    }

    protected function getCurrentVersion()
    {
        $configFile = file_get_contents($this->publicPath . 'config.php');
        preg_match('/\(\'G5_GNUBOARD_VER\',\s\'([0-9.]+)\'\)\;/', $configFile, $match);
        if (isset($match[1])) {
            return $match[1];
        } else {
            throw new Exception("config 파일에서 버전을 찾을 수 없습니다.");
        }
    }

    protected function getNextVersion()
    {
        $versionList = $this->versionList;
        ksort($versionList);
        foreach ($versionList as $version => $data) {
            if ($version > $this->current) {
                return $version;
            }
        }
    }
    
    protected function getpreviousVersion()
    {
        $versionList = $this->versionList;
        krsort($versionList);
        foreach ($versionList as $version => $data) {
            if ($version < $this->current) {
                return $version;
            }
        }
    }

    public function current()
    {
        return $this->versionList[$this->current];
    }

    public function next()
    {
        return $this->versionList[$this->next];
    }

    public function previous()
    {
        return $this->versionList[$this->previous];
    }

    public function getPublicPath()
    {
        return $this->publicPath;
    }
}
