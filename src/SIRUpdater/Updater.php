<?php

namespace silnex\SIRUpdater;

use Exception;
use PharData;
use silnex\Util\Diff;
use silnex\Util\Downloader;
use silnex\Util\Helper;

class Updater
{
    protected $versionManager;
    protected $publicPath, $basePath, $backupPath;
    protected $current, $next, $previous;
    protected $withSkin = false, $withTheme = false;

    public function __construct(VersionManager $versionManager)
    {
        $this->versionManager = $versionManager;
        $this->publicPath = $versionManager->getPublicPath();
        $this->current = $this->versionManager->current();
        $this->next = $this->versionManager->next();
        // $this->previous = $this->versionManager->previous();
        $this->setOption();
    }

    public function setOption(array $options = [])
    {
        $this->basePath = isset($options['path']['base']) ? $options['path']['base'] : ($this->publicPath . '../');
        $this->backupPath = $this->basePath . (isset($options['path']['backup']) ? $options['path']['backup'] : 'backup');
        $this->withSkin = isset($options['withSkin']) ?: false;
        $this->withTheme = isset($options['withTheme']) ?: false;
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

            $storePath = $this->basePath . str_replace('.', '_', $version['version']) . '/' . $type;

            $this->download($downloadLink, $fileName, $storePath);

            return $storePath . '/' . $fileName;
        } else {
            throw new Exception("잘못된 타입을 요청 하였습니다\n허용된 타입: full, patch\n요청된 타입:{$type}\n");
        }
    }

    protected function downloadNext(string $type = 'patch')
    {
        return $this->downloadCode($type, $this->next);
    }

    protected function downloadCurrent(string $type = 'full')
    {
        return $this->downloadCode($type, $this->current);
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

    protected function getPreviousPath($postFix = false)
    {
        return $this->getPath($this->previous, $postFix);
    }

    protected function getPublicPath($postFix = false)
    {
        $postFix = Helper::startSeparator($postFix);
        return rtrim($this->publicPath, '/') . $postFix;
    }

    protected function getPath(array $version, $postFix = false)
    {
        $postFix = Helper::startSeparator($postFix);
        return $this->basePath . str_replace('.', '_', $version['version']) . $postFix;
    }
    
    protected function removeBasePath(string $path)
    {
        return str_replace($this->publicPath, '', str_replace($this->basePath, '', $path));
    }

    protected function clear($withBackup = false)
    {
        if ($withBackup) {
            Helper::rmrf($this->backupPath);
        }
        Helper::rmrf($this->getNextPath());
        Helper::rmrf($this->getCurrentPath());
        Helper::rmrf($this->basePath . '/5_*');
    }

    protected function readyForUpdate()
    {
        try {
            if (!is_dir($this->getNextPath('patch'))) {
                $this->extract($this->downloadNext(), true);

                if (!$this->withSkin) {
                    echo "스킨 폴더 삭제\n";
                    Helper::rmrf($this->getNextPath('full/skin'));
                    Helper::rmrf($this->getNextPath('full/mobile/skin'));
                }

                if (!$this->withTheme) {
                    echo "테마 폴더 삭제\n";
                    Helper::rmrf($this->getNextPath('full/theme'));
                }
            } elseif (!is_dir($this->getCurrentPath('full'))) {
                $this->extract($this->downloadCurrent(), true);

                if (!$this->withSkin) {
                    echo "스킨 폴더 삭제\n";
                    Helper::rmrf($this->getCurrentPath('full/skin'));
                    Helper::rmrf($this->getCurrentPath('full/mobile/skin'));
                }

                if (!$this->withTheme) {
                    echo "테마 폴더 삭제\n";
                    Helper::rmrf($this->getCurrentPath('full/theme'));
                }
            }
            echo "업데이트에 필요한 파일이 모두 다운로드 되었습니다.\n";
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    protected function getPatchFiles()
    {
        $nextPatchPath = $this->getNextPath('patch');
        return str_replace($nextPatchPath, '', Helper::scanFiles($nextPatchPath));
    }

    public function diffCheck()
    {
        $originalPath = $this->getCurrentPath('full');
        $publicPath = $this->getPublicPath();

        $this->readyForUpdate();

        $patchFiles = $this->getPatchFiles();
        $diffFiles = [];

        foreach ($patchFiles as $patchFile) {
            $originFile = $originalPath . $patchFile;
            $publicFile = $publicPath . $patchFile;

            if (Diff::isDiff($originFile, $publicFile)) {
                $diffFiles[$originFile] = $publicFile;
            }
        }

        return $diffFiles;
    }

    protected function cover(array $filesPath, string $sourceBasePath, string $destBasePath)
    {
        foreach ($filesPath as $patchFile) {
            $sourceFile = $sourceBasePath . $patchFile;
            $destFile = $destBasePath . $patchFile;
            $path = pathinfo($destFile);
            if (!file_exists($path['dirname'])) {
                mkdir($path['dirname'], 0777, true);
            }
            echo $this->removeBasePath($destFile) . " 파일을 " . $this->removeBasePath($sourceFile) . "로 덮어씌웠습니다.\n";
            copy($sourceFile, $destFile);
        }
    }

    protected function upgrade()
    {
        $patchFiles = $this->getPatchFiles();
        $nextPath = $this->getNextPath('patch');
        $publicPath = $this->getPublicPath();

        $this->cover($patchFiles, $nextPath, $publicPath);
    }

    public function backup()
    {
        if (is_dir($this->backupPath)) {
            echo "이미 백업 폴더가 있습니다.\n백업 폴더를 삭제후 진행해주세요\n";
            exit;
        }
        $patchFiles = $this->getPatchFiles();
        $publicPath = $this->getPublicPath();

        $this->cover($patchFiles, $publicPath, $this->backupPath);
    }

    public function restore()
    {
        if (!is_dir($this->backupPath)) {
            echo "백업파일이 없습니다.\n";
            exit;
        }
        $patchFiles = str_replace($this->backupPath, '', Helper::scanFiles($this->backupPath));
        $publicPath = $this->getPublicPath();

        $this->cover($patchFiles, $this->backupPath, $publicPath);
    }

    public function update($force = false, $withClear = false)
    {
        $diff = $this->diffCheck();
        if ($force || empty($diff)) {
            $this->backup();
            $this->upgrade();
        } else {
            echo "다음 항목에 다른점이 있습니다.\n";
            foreach ($diff as $file1 => $file2) {
                Diff::displayDiff($file1, $file2);
            }
        }

        if ($withClear) {
            $this->clear();
        }
    }
    
    /**
     * Todo
     * diff public path files
     * backup diff files
     * install update with force option
     * clear update files
     * clear backup files
     * rollback from backup files
     * show diff files
     * show diff lines
     * 
     */
}
