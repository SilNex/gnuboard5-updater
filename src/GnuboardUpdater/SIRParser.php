<?php

namespace Silnex\GnuboardUpdater;

use Silnex\Util\Curl;

class SIRParser
{
    protected $sirBoardPattern = '/<a\shref=\"(.*)\"\sclass="title_link">\s+\[?(보안패치|정식버전|베타버전)?\]?\s?그누보드\s?(5\.4\.[0-9]\.?[0-9]?)/';
    protected $sirAttachPattern = '/onclick=\"file_download\(\'(\/\/sir\.kr\/bbs\/download\.php.*)\',\s\'gnuboard5\.4\.[0-9]\.?[0-9]?\.patch\.tar\.gz\'\);\"/';
    protected $githubUriPattern = '/>(https:\/\/github\.com\/gnuboard\/gnuboard5\/commit\/[a-z0-9]+)</';

    public function __construct()
    {
        $this->url = "https://sir.kr/g5_pds";
        $this->curl = new Curl();
    }

    public function getPostList()
    {
        $response = $this->curl->get($this->url)->toString();
        preg_match_all($this->sirBoardPattern, $response, $posts);
        for ($i = 0; $i < count($posts[0]); $i++) {
            $postList[] = [
                'href' => "https:" . $posts[1][$i],
                'info' => $posts[2][$i],
                'version' => $posts[3][$i]
            ];
        }
    }
}
