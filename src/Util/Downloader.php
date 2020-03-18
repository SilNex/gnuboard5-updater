<?php

namespace silnex\Util;

use Exception;

class Downloader
{
    static public function download(string $downloadLink, string $fileName, string $storePath = '/tmp')
    {
        if (!is_dir($storePath)) {
            mkdir($storePath, 0777, true);
        } elseif (count(scandir($storePath)) !== 2) {
            throw new Exception("{$storePath}는 비어있는 폴더가 아닙니다.\n");
        }

        $filePath = $storePath . '/' . $fileName;
        if (file_exists($filePath)) {
            throw new Exception("{$filePath}파일이 이미 존재합니다.\n");
        }

        $curl = new Curl;
        $data = $curl->get($downloadLink);
        if (!file_put_contents($storePath . '/' . $fileName, $data)) {
            throw new Exception("{$filePath}에 저장을 실패했습니다.\n");
        }
    }
}