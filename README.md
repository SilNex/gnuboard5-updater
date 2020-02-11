# GNUBOARD5 버전 업데이트 플러그인
[그누보드](https://sir.kr/main/g5/)의 업데이트를 간소화 하기위한 플러그인 입니다.  

## 목표
최대한 PSR를 지키며 개발  
최소 PHP 5.6.x 버전 까지 지원  
최대한 간편한 설치  
누구나 수정 배포가 가능하며 상업적이용이 가능합니다.

## 사용법
`html` 와 같은 위치에 파일을 넣고 `php updater.php`로 실행 시켜줍니다.

### 설정 방법
`updaterConfig.php`에 `__GNU_DIR__`에 `html`을 그누보드가 설치된 경로로 변경합니다.

### 스크립트
```shell
cd /var/www/
git clone https://github.com/SilNex/gnuboard5-updater.git
mv ./gnuboard5-updater/* ./
php updater.php
```

## Todo
- [x] sir.kr로 부터 그누보드 버전 리스트 가져오기
- [x] 현재, 다음, 최신버전 정보 정렬
- [x] 버전에 대한 다운로드 링크 가져오기
- [x] 버전에 대한 github링크 가져오기
- [x] 패치 파일 다운로드 받기
- [x] skin, theme 폴더 삭제
- [x] 수정된 파일 리스트 가져오기
- [x] 현재 버전의 원본 파일 다운로드
- [x] 현재 버전의 원본 파일과 현재 설치된 버전의 파일이 다른지 비교
- [x] [같을 경우] 덮어 씌우기
- [x] [다를 경우] 다른 파일 목록 가져오기
- [x] 백업 파일에서 복구
- [x] Updater class 리펙토링
- [ ] updater cli 만들기
- [ ] 그누보드 관리자 UI만들기
- ETC . . .

## 잡설
나름 신경쓰면서 만들었는데 생각보다 코드가 더럽다..