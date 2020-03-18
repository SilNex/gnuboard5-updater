<?php

namespace silnex\Util;

use Exception;

class Downloader
{
    static public function download(string $downloadLink, string $fileName, string $storePath = '/tmp')
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