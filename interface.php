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
     * @return SIRParser
     */
    public function parseVersionList();

    /**
     * 해당 버전에 대한 자세한 정보(download link, github link) 수집.
     * 
     * @return SIRParser
     */
    public function parseDetail();

    /**
     * 현재 버전의 패치파일을 다운로드합니다.
     * 
     * @return SIRParser
     */
    public function patchDownload();

    /**
     * @param string $path 저장 경로
     * @param string $url 다운로드 링크
     * @param string $fileName 파일명
     * $url의 파일을 $path에 $fileName으로 저장함
     * 
     * @return string 다운로드한 파일 경로
     */
    public function download($path, $url, $fileName);

    /**
     * 현재 버전의 패치파일을 압축해제
     * skin, theme, mobile/skin을 삭제
     * 
     * @return string 압축해제 경로
     */
    public function extractPatchFile();

    /**
     * @param string $path 압축 해제 경로
     * 
     * @return $path 압축해제 경로
     */
    public function extract(&$tarHeader, $path = null);

    /**
     * @param string $path 경로 안에 모든 모든 파일을 삭제한다.
     * 
     * @return void
     */
    static public function rmrf($path);
}