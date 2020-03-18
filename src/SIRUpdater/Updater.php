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
            echo "{$data['version']}을 다운로드 하는중...\n";
            $downloadLink = $data['detail'][$type];

            $fileName = $data['version'] . ($type === 'full' ?: '.patch') . '.tar.gz';

            $storePath = $this->basePath . str_replace('.', '_', $data['version']) . DIRECTORY_SEPARATOR . $type;

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
        return $tarPath;
    }

    public function readyForUpdate($withSkin = false, $withTheme = false)
    {
        $nextVersionPath = $this->extract($this->downloadNext(), true);
        $currentVersionPath = $this->extract($this->downloadCurrent(), true);
        
        if (!$withSkin) {
            echo "스킨 폴더 삭제\n";
            Helper::rmrf($currentVersionPath . '/skin');
            Helper::rmrf($currentVersionPath . '/mobile/skin');
        }
        
        if (!$withTheme) {
            echo "테마 폴더 삭제\n";
            Helper::rmrf($currentVersionPath . '/theme');
        }

        echo "업데이트에 필요한 파일이 모두 다운로드 되었습니다.\n";
    }
}
