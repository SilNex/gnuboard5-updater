<?php
interface SIRParserInterface
{
    /**
     * html페이지를 가져온다.
     * 
     * @return string
     */
    public function get($url, $param = []);

    /**
     * 다운로드 페이지에서 Version 클래스를 반환한다.
     * 
     * @return VersionInterface
     */
    public function parseVersionList();
}

interface VersionInterface
{
    /**
     * 최신, 다음, 현재 Version 클래스를 반환한다.
     * 
     * @return VersionInterface
     */
    public function getLatest();
    public function getNext();
    public function getCurrent();
}
