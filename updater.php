<?php
require (__DIR__ . '/interface.php');
require (__DIR__ . '/class.php');


// class GnuboardUpdater
// {
//     protected $sirUri = 'https://sir.kr';
//     protected $boradUri = '/g5_pds';
//     public $versionList = null;
//     public $currentVer = null;
//     public $latestVer = null;

//     public function __construct()
//     {
//         $this->setVersionList();
//         $this->setCurrentVer();
//         $this->setNextVer();
//         $this->setLatestVer();
//     }

//     /**
//      * @param string $uri
//      * @param null|array $param
//      * 리퀘스트의 결과에 따라 json object 혹은 string을 반환한다.
//      * 
//      * @return object|string
//      */
//     public function getHtml($uri, $param = null)
//     {
//         if (is_array($param)) {
//             $query = http_build_query($param);
//             $uri .= "?{$query}";
//         }

//         if (extension_loaded('curl')) {
//             $ch = curl_init($uri);

//             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//             curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
//             curl_setopt($ch, CURLOPT_USERAGENT, 'gnuboard-updater');

//             $response = curl_exec($ch);

//             curl_close($ch);

//             if ($response !== false) {
//                 return $response;
//             }
//         } else {
//             return file_get_contents($uri);
//         }
//     }

//     /**
//      * @param string $version
//      * 버전 string을 int형으로 변환해주는 메소드
//      * 
//      * @return int
//      */
//     public function verToInt($version)
//     {
//         return str_replace('.', '', $version);
//     }

//     /**
//      * 그누보드 버전 목록을 설정하는 메소드
//      * 
//      * @return string
//      */
//     public function setVersionList()
//     {
//         $versions = [];
//         $page = 1;
//         do {
//             $html = $this->getDownloadHtml($page);
//             foreach ($html->find('.title_link') as $title) {
//                 if (!preg_match('/그누보드\s*(5\.[0-9]\.[0-9]\.[0-9])/', $title->innertext, $match)) {
//                     continue;
//                 }
//                 $ver = $match[1];
//                 $securityUpdate = preg_match('/보안패치/', $title->innertext) ? true : false;
//                 $betaUpdate = preg_match('/베타버전/', $title->innertext) ? true : false;
//                 if ($betaUpdate) {
//                     continue;
//                 }
//                 $versions[$this->verToInt($ver)] = [
//                     'version' => $ver,
//                     'link' => $title->href,
//                     'security' => $securityUpdate,
//                 ];
//             }
//             krsort($versions);
//             $page++;
//         } while (array_key_last($versions) > 5400);

//         // 1차 목표는 5.4 까지 지원
//         foreach ($versions as $key => $value) {
//             if ($key < 5400) {
//                 unset($versions[$key]);
//             }
//         }
//         $this->versionList = $versions;
//     }

//     /**
//      * 그누보드 현재 버전 정보를 설정하는 메소드
//      * 
//      * @return void
//      */
//     public function setCurrentVer()
//     {
//         $intVer = $this->verToInt($this->getCurrentVer());
//         $this->currentVer = $this->versionList[$intVer];
//     }

//     /**
//      * 그누보드 다음 버전 정보를 설정하는 메소드
//      * 
//      * @return void
//      */
//     public function setNextVer()
//     {
//         $intVer = $this->verToInt($this->getCurrentVer());
//         $this->currentVer = $this->versionList[$intVer];
//     }
    
//     /**
//      * 그누보드 최신 버전 정보를 설정하는 메소드
//      * 
//      * @return void
//      */
//     public function setLatestVer()
//     {
//         $intVer = $this->verToInt($this->getLatestVer());
//         $this->latestVer = $this->versionList[$intVer];
//     }

//     /**
//      * 그누보드 현재 버전을 가져오는 메소드
//      * 
//      * @return string
//      */
//     public function getCurrentVer()
//     {
//         $configString = file_get_contents('./config.php');
//         preg_match('/\(\'G5_GNUBOARD_VER\',\s\'([0-9.]+)\'\)\;/', $configString, $match);
//         return $match[1];
//     }

//     /**
//      * @param int $page
//      * 그누보드 홈페이지에 다운로드 목록을 가져오는 기능
//      * 
//      * @return simple_html_dom
//      */
//     public function getDownloadHtml($page)
//     {
//         $html = $this->getHtml($this->sirUri . $this->boradUri, [
//             'page' => $page
//         ]);
//         return str_get_html($html);
//     }

//     /**
//      * 그누보드 최신 버전을 가져오는 메소드
//      * 
//      * @return string
//      */
//     public function getLatestVer()
//     {
//         foreach ($this->versionList as $value) {
//             return $value;
//         }
//     }

//     /**
//      * 그누보드 다음 버전을 가져오는 메소드
//      * 
//      * @return string
//      */
//     public function getNextVer()
//     {
//         $intVar = $this->verToInt($this->currentVer['version']);
//         $tmpVerlist = $this->versionList;
//         ksort($tmpVerlist);
//         foreach ($tmpVerlist as $key => $value) {
//             if ($intVar < $key) {
//                 return $value;
//             }
//         }
//     }

//     /**
//      * 현재 설치된 버전이 최신버전인지 체크
//      * 
//      * @return string|null
//      */
//     public function checkForUpdate()
//     {
//         if ($this->currentVer['version'] === $this->latestVer['version']) {
//             return null;
//         } else {
//             return "현재 버전: {$this->currentVer['version']} \n다음 버전: {$this->nextVer['version']} \n최신 버전: {$this->latestVer['version']}";
//         }
//     }
// }

// // TEST
// $gnup = new GnuboardUpdater();

// var_dump($gnup->checkForUpdate());
