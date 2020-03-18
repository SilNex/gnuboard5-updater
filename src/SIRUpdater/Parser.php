<?php

namespace silnex\SIRUpdater;

use IntlException;
use InvalidArgumentException;
use silnex\Util\Curl;

class Parser
{
    protected $postListPattern;
    protected $postAttatchPattern;
    protected $githubUriPattern = '/>(https:\/\/github\.com\/gnuboard\/gnuboard5\/commit\/[a-z0-9]+)</';

    protected $postList = [];
    protected $postAttach = [];

    public function __construct(ParserFactoryInterface $parser)
    {
        $this->url = $parser->getUrl();
        $this->postListPattern = $parser->getPostListPattern();
        $this->postAttatchPattern = $parser->getPostAttatchPattern();

        $this->curl = new Curl();
        $this->postList = $this->parsePostList();
        ksort($this->postList);
    }

    protected function versionFormat(string $versionString)
    {
        if (preg_match('/^(5\.4\.[0-9]\.[0-9])$/', $versionString)) {
            return $versionString;
        } elseif (preg_match('/^(5\.4\.[0-9])$/', $versionString)) {
            return $versionString . '.0';
        } else {
            throw new InvalidArgumentException("{$versionString}은 정상적인 포맷의 버전이 아닙니다.");
        }
    }

    protected function matches(string $pattern, ?string $url, string $response = null)
    {
        if (isset($url) && is_null($response)) {
            $response = $this->get($url);
        }
        preg_match_all($pattern, $response, $mataches);
        return $mataches;
    }

    protected function parsePostList()
    {
        $posts = $this->matches($this->postListPattern, $this->url);

        for ($i = 0; $i < count($posts[0]); $i++) {
            try {
                $version = $this->versionFormat($posts[3][$i]);
            } catch (InvalidArgumentException $e) {
                die($e->getMessage());
            }

            $this->postList[$version] = [
                'href' => "https:" . $posts[1][$i],
                'type' => $posts[2][$i],
                'version' => $version,
            ];
        }

        return $this->postList;
    }

    protected function get(string $url)
    {
        return $this->curl->get($url);
    }

    public function parsePostAttachFiles(string $version)
    {
        if ($version < '5.4.0.0') {
            throw new InvalidArgumentException("그누보드 버전 5.4 이상만 지원합니다.");
        } elseif (!preg_match('/^(5\.4\.[0-9]\.?[0-9]?)$/', $version)) {
            throw new InvalidArgumentException("버전은 5.4.x.x 와 같은 양식으로 입력해야합니다.");
        }

        $attachFiles = $this->matches($this->postAttatchPattern, $this->postList[$version]['href']);

        for ($i = 0; $i < 2; $i++) {
            $downloadLink = 'https:' . str_replace('download', 'download2', html_entity_decode($attachFiles[1][$i]));
            if (strpos($attachFiles[0][$i], 'patch')) {
                $patchLink = $downloadLink;
            } else {
                $fullLink = $downloadLink;
            }
        }

        return [
            $version => [
                'patch' => $patchLink,
                'full' => $fullLink,
            ],
        ];
    }

    public function getPostList()
    {
        return $this->postList;
    }
}
