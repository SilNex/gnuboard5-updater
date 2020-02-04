<?php
require('./simple_html_dom.php');
class GnuboardUpdater
{
    protected $sirUri = 'https://sir.kr/';
    protected $gnuBoradUri = '/g5_pds';
    public $versionList = null;
    public $currentVer = null;
    public $latestVer = null;

    public function __construct()
    {
        $this->sirGnu = $this->getHtml('/home/silnex/tmp/sir.html');
        // $this->sirUri . $this->gnuBoradUri
        
        $this->versionList = $this->getVerList();

        $this->currentVer = $this->getCurrentVer();
        $this->latestVer = $this->getLatestVer();
        $this->nextVer = $this->getNextVer();
    }

    /**
     * @param string $uri
     * @param null|array $param
     * 리퀘스트의 결과에 따라 json object 혹은 string을 반환한다.
     * 
     * @return object|string
     */
    public function getHtml($uri, $param = null)
    {
        if (is_array($param)) {
            $query = http_build_query($param);
            $uri .= "?{$query}";
        }
        return file_get_html($uri);
    }

    /**
     * 그누보드 현재 버전을 가져오는 기능
     * 
     * @return string
     */
    public function getCurrentVer()
    {
        $configString = file_get_contents('./config.php');
        preg_match('/\(\'G5_GNUBOARD_VER\',\s\'([0-9.]+)\'\)\;/', $configString, $match);
        return $match[1];
    }

    /**
     * 그누보드 최신 버전을 가져오는 기능
     * 
     * @return string
     */
    public function getLatestVer()
    {
        foreach ($this->versionList as $value) {
            return $value;
        }
    }

    /**
     * 그누보드 버전 목록을 가져오는 기능
     * 
     * @return string
     */
    public function getVerList()
    {
        $versions = [];
        $page = 1;
        do {
            foreach ($this->sirGnu->find('.title_link') as $title) {
                if (!preg_match('/그누보드\s*(5\.[0-9]\.[0-9]\.[0-9])/', $title->innertext, $match)) {
                    continue;
                }
                $versions[str_replace('.', '', $match[1])] = $match[1];
            }
            krsort($versions);
            $page++;
        } while (array_key_last($versions) > 5400);
        return $versions;
    }

    /**
     * 그누보드 다음 버전을 가져오는 기능
     * 
     * @return string
     */
    public function getNextVer()
    {
        $currentVer = str_replace('.', '', $this->currentVer);
        $tmpVerlist = $this->versionList;
        ksort($tmpVerlist);
        foreach ($tmpVerlist as $key => $value) {
            if ($currentVer < $key) {
                return $value;
            }
        }
    }

    /**
     * 현재 설치된 버전이 최신버전인지 체크
     * 
     * @return string|null
     */
    public function checkForUpdate()
    {
        if ($this->currentVer === $this->latestVer) {
            return null;
        } else {
            return "현재 버전: {$this->currentVer} \n다음 버전: {$this->nextVer} \n최신 버전: {$this->latestVer}";
        }
    }
}

// TEST
$gnup = new GnuboardUpdater();

echo $gnup->checkForUpdate();
