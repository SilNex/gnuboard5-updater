# GNUBOARD 업데이트 플러그인
그누보드의 업데이트를 자동화 해주는 패키지입니다.
  
# 지원 환경
 * PHP 7.0 버전 이상
 * 그누보드 5.4 이상

# Installation

**해당 패키지는 반드시 공개된 폴더 상위에 설치되어야 합니다.**

## Composer를 이용한 방법
    cd public_html/../
    composer require silnex/sir-updater

## 직접 다운로드 하는 방법
    cd public_html/../
    git clone -b with-vendor --single-branch https://github.com/SilNex/sir-updater


# Usage

## 기본적인 사용법
```php
<?php
require_once 'vendor/autoload.php';

use silnex\SIRUpdater\Updater;

$updater = new Updater('/your/public/html/directory');
$updater->update();
```

## 옵션 및 메소드
```php
$updater = new Updater('/your/public/html/directory');
/**
 * @param bool $force = 변경사항을 무시하고 덮어씌우기
 * @param bool $withClear = 업데이트를 완료하고 업데이트에 사용된 파일 삭제
 */
$updater->update(true, true);
$update->restore(); // 업데이트시 생성된 backup으로 복구
$update->diffCheck(); // 현재 버전의 원본과 다른 파일 정보를 가져옴
```


# Todo
 - [x] 좀더 init 과정 심플하게 바꾸기
 - [x] 옵션 설명 추가하기
