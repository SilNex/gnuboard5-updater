<?php

namespace silnex\SIRUpdater;

class 111111111 implements ParserFactoryInterface
{
    protected $url;
    protected $postAttatchPattern;
    protected $postListPattern;

    public function __construct()
    {
        $this->url = 'https://sir.kr/g5_pds';
        $this->postAttatchPattern = '/onclick=\"file_download\(\'(\/\/sir\.kr\/bbs\/download\.php.*)\',\s\'gnuboard5\.4\.[0-9]\.?[0-9]?(\.patch)?\.tar\.gz\'\);\"/';
        $this->postListPattern = '/<a\shref=\"(.*)\"\sclass="title_link">\s+\[?(보안패치|정식버전|베타버전)?\]?\s?그누보드\s?(5\.4\.[0-9]\.?[0-9]?)/';
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getPostAttatchPattern()
    {
        return $this->postAttatchPattern;
    }

    public function getPostListPattern()
    {
        return $this->postListPattern;
    }

}