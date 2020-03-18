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
    protected $basePath, $publicPath;
    protected $current;

    public function __construct(string $publicPath, string $type = 'Gnuboard')
    {
        $this->publicPath = $publicPath . DIRECTORY_SEPARATOR;
        $this->basePath = $publicPath . '..' . DIRECTORY_SEPARATOR;

        if (!is_dir($this->publicPath)) {
            throw new Exception("{$this->publicPath}\n경로가 잘못되었습니다.\n");
        } elseif (!file_exists($this->publicPath . 'config.php')) {
            throw new Exception("{$this->publicPath}\nconfig.php 파일을 찾을 수 없습니다.\n");
        }

        $this->current = $this->getCurrentVersion();

        if ($type === 'Gnuboard') {
            $this->parser = new Parser(new GnuboardParserFactory());
        } elseif ($type === 'YoungCart') {
            // $this->parser = new YoungCartParserFactory();
            throw new Exception("영카트는 현재 지원되지 않습니다.\n");
        } else {
            throw new InvalidArgumentException("type은 [Gnuboard, YoungCart]만 허용 됩니다.\n");
        }
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
            if ($version > $this->current) {
                return $version;
            }
        }
    }

    public function current()
    {
        return $this->current;
    }

    public function next()
    {
        return $this->next;
    }

    public function previous()
    {
        
    }
}
