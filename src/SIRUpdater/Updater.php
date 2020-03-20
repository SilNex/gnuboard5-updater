<?php

namespace silnex\SIRUpdater;

use Exception;
use PharData;
use silnex\Util\Downloader;
use silnex\Util\Helper;

class Updater
{
    protected $versionManager;
    protected $publicPath, $basePath, $backupPath;
    protected $current, $next, $previous;

    public function __construct(VersionManager $versionManager, $baseDir = null, $backupDir = null)
    {
        $this->versionManager = $versionManager;
        $this->publicPath = $versionManager->getPublicPath();
        $this->current = $this->versionManager->current();
        $this->next = $this->versionManager->next();
        // $this->previous = $this->versionManager->previous();

        $pathOption = [];
        if ($baseDir) {
            $pathOption['base'] = $baseDir;
        }

        if ($backupDir) {
            $pathOption['backup'] = $baseDir;
        }

        $this->setPathInfo($pathOption);
    }

    protected function setPathInfo(array $pathOption = [])
    {
        $this->basePath = (isset($pathOption['base']) ? $pathOption['base'] : ($this->publicPath . '..' . DIRECTORY_SEPARATOR));
        $this->backupPath = $this->basePath . (isset($pathOption['backup']) ? $pathOption['backup'] : 'backup');
    }

    /**
     * @param string $type = 'full'|'patch'
     * @param array $version = ['detail' => ['full' => href, 'patch' => href]]
     * 
     * @return string $storeFilePath
     */
    protected function downloadCode(string $type, array $version)
    {
        if (in_array($type, ['full', 'patch'])) {
            echo "{$version['version']}을 다운로드 하는중...\n";
            $downloadLink = $version['detail'][$type];

            $fileName = $version['version'] . ($type === 'full' ?: '.patch') . '.tar.gz';

            $storePath = $this->basePath . str_replace('.', '_', $version['version']) . DIRECTORY_SEPARATOR . $type;

            $this->download($downloadLink, $fileName, $storePath);

            return $storePath . DIRECTORY_SEPARATOR . $fileName;
        } else {
            throw new Exception("잘못된 타입을 요청 하였습니다\n허용된 타입: full, patch\n요청된 타입:{$type}\n");
        }
    }

    protected function downloadNext()
    {
        return $this->downloadCode('patch', $this->next);
    }

    protected function downloadCurrent()
    {
        return $this->downloadCode('full', $this->current);
    }

    protected function download(string $downloadLink, string $fileName, string $storePath = '/tmp')
    {
        Downloader::download($downloadLink, $fileName, $storePath);
    }

    protected function extract(string $tarFile, bool $remove = false)
    {
        echo "압축 해제하는 중...\n";
        $tarPath = pathinfo($tarFile)['dirname'];
        $tar = new PharData($tarFile);
        $tar->extractTo($tarPath);
        if ($remove) {
            echo "압축파일 삭제 중...\n";
            unlink($tarFile);
        }
    }

    protected function getNextPath($postFix = false)
    {
        return $this->getPath($this->next, $postFix);
    }

    protected function getCurrentPath($postFix = false)
    {
        return $this->getPath($this->current, $postFix);
    }

    protected function getPrevious($postFix = false)
    {
        return $this->getPath($this->previous, $postFix);
    }

    protected function getPath(array $version, $postFix = false)
    {
        if ($postFix && substr($postFix, 0, 1) !== '/') {
            $postFix .= '/' . $postFix;
        }
        return $this->basePath . str_replace('.', '_', $version['version']) . $postFix;
    }

    protected function clear($withBackup = false)
    {
        if ($withBackup) {
            Helper::rmrf($this->backupPath);
        }
        Helper::rmrf($this->getNextPath());
        Helper::rmrf($this->getCurrentPath());
        Helper::rmrf($this->basePath . '/5_4_*');
    }

    protected function readyForUpdate($withSkin = false, $withTheme = false)
    {
        $this->extract($this->downloadNext(), true);
        $this->extract($this->downloadCurrent(), true);

        if (!$withSkin) {
            echo "스킨 폴더 삭제\n";
            Helper::rmrf($this->getCurrentPath('full/skin'));
            Helper::rmrf($this->getCurrentPath('full/mobile/skin'));
        }

        if (!$withTheme) {
            echo "테마 폴더 삭제\n";
            Helper::rmrf($this->getCurrentPath('full/theme'));
        }

        echo "업데이트에 필요한 파일이 모두 다운로드 되었습니다.\n";
    }

    public function update($withSkin = false, $withTheme = false)
    {
        try {
            $this->readyForUpdate($withSkin, $withTheme);
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        array_map(
            function ($key, $item) {
                var_dump($key, $item);
            },
            Helper::scanFiles($this->nextVersionPath)
        );

        // $this->clear();
    }
}
