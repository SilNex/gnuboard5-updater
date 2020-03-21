# GNUBOARD 업데이트 플러그인
그누보드의 업데이트를 자동화 해주는 패키지입니다.
  
# 지원 환경
 * PHP 7.0 버전 이상
 * 그누보드 5.4 이상

# Installation

**해당 패키지는 반드시 공개된 폴더 상위에 설치되어야 합니다.**

## Composer를 이용한 방법
    cd /public_html/../
    composer require silnex/sir-updater

## 직접 다운로드 하는 방법
    cd /public_html/../
    git clone -b with-vendor --single-branch https://github.com/SilNex/sir-updater


# Usage

```php
<?php
$gnuFactory = new GnuboardParserFactory();
$parser = new Parser($gnuFactory);
$vm = new VersionManager('/your/public/html/directory', $parser);
$updater = new Updater($vm);
$updater->update(true);
```

# Todo
 - [ ] 좀더 init 과정 심플하게 바꾸기