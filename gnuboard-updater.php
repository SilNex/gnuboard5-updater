<?php

/**
 * @param string $string
 * json 형식을 채크한다.
 * 
 * @return boolean
 */
function isJson($string)
{
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

class GnuboardGithub
{
    protected $baseUri = 'https://api.github.com/repos/gnuboard';
    protected $gnuRepoUri;
    protected $gnuCommitUri;

    public function __construct()
    {
        $this->gnuRepoUri = $this->baseUri . '/gnuboard5';
        $this->gnuCommitUri = $this->gnuRepoUri . '/commits';
    }

    /**
     * @param string $uri
     * @param null|array $param
     * 리퀘스트의 결과에 따라 json object 혹은 string을 반환한다.
     * 
     * @return object|string
     */
    public function get($uri, $param = null)
    {
        if (extension_loaded('curl')) {
            if (is_array($param)) {
                $query = http_build_query($param);
                $uri .= "?{$query}";
            }
            $ch = curl_init($uri);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_USERAGENT, 'gnuboard-updater');

            $response = curl_exec($ch);

            curl_close($ch);

            if ($response !== false) {
                return isJson($response) ? json_decode($response) : $response;
            }
        }
        throw new Exception('php-curl 패키지를 설치해주세요.');
    }

    /**
     * @param string $path
     * commit list를 가져온다.
     * $path가 설정 되어있을경우 $path에 commit list를 가져온다
     * 
     * @return object
     */
    public function getCommitList($path = null)
    {
        $path = $path ? ['path' => $path] : null;
        return $this->get($this->gnuCommitUri, $path);
    }
}

class GnuboardUpdater
{
    protected $currentVer = null;
    protected $latestVer = null;
    protected $github = null;

    public function __construct()
    {
        $this->github = new GnuboardGithub();
        $this->currentVer = $this->getCurrentVer();
        $this->latestVer = $this->getLatestVer();
    }

    /**
     * 그누보드 최신 버전을 가져오는 기능
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
        preg_match(
            '/(5\.[0-9]\.[0-9]\.[0-9])/',
            $this->github->getCommitList('/config.php')[0]->commit->message,
            $matches
        );
        return $matches[1];
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
            return "현재 버전: {$this->currentVer} \n최신 버전: {$this->latestVer}";
        }
    }
}

// TEST
$gnup = new GnuboardUpdater();

echo $gnup->checkForUpdate();
