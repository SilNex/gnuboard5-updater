<?php
class Version extends SplDoublyLinkedList implements VersionInterface
{
    protected function getCurrentVersionString()
    {
        $configString = file_get_contents(__DIR__ . '/config.php');
        preg_match('/\(\'G5_GNUBOARD_VER\',\s\'([0-9.]+)\'\)\;/', $configString, $match);
        return $match[1];
    }

    public function findInfo($version)
    {
        for ($i = 0; $i < $this->count(); $i++) {
            if ($version === $this->offsetGet($i)->version) {
                return $this->offsetGet($i);
            }
        }
        return null;
    }

    public function getLatest()
    {
        return $this->bottom();
    }

    public function getNext()
    {
        $version = $this->getCurrentVersionString();
        for ($i = 0; $i < $this->count(); $i++) {
            if ($version < $this->offsetGet($i)->version) {
                return $this->offsetGet($i);
            }
        }
        return null;
    }

    public function getCurrent()
    {
        return $this->findInfo($this->getCurrentVersionString());
    }
}

class SIRParser extends Version implements SIRParserInterface
{
    protected $sirBoardPattern = '/<a\shref=\"(.*)\"\sclass="title_link">\s+\[?(보안패치|정식버전|베타버전)?\]?\s?그누보드\s?(5\.4\.[0-9]\.[0-9])/';

    public function get($url, $param = [])
    {
        if (empty($param)) {
            $query = http_build_query($param);
            $url .= "?{$query}";
        }

        if (extension_loaded('curl')) {
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_USERAGENT, 'gnuboard-updater');

            $response = curl_exec($ch);

            curl_close($ch);

            if ($response !== false) {
                return $response;
            }
        } else {
            return file_get_contents($url);
        }
    }

    public function parseVersionList()
    {
        if ($this->isEmpty()) {
            do {
                $uri = "https://sir.kr/g5_pds";
                $page = 1;
                $response = $this->get($uri, ['page' => $page]);
                preg_match_all($this->sirBoardPattern, $response, $matches);
                for ($i = 0; $i < count($matches[0]); $i++) {
                    $this->push((object) [
                        'href' => $matches[1][$i],
                        'info' => $matches[2][$i],
                        'version' => $matches[3][$i],
                    ]);
                }
                $page++;
            } while ($this->top()->version < '5.4.0.0');
        }
    }

    public function parseDetail()
    {
        if ($this->isEmpty()) {
            $this->parseVersionList(); 
        }
        
    }
}
