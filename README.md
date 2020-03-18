# GNUBOARD5 버전 업데이트 플러그인
[그누보드](https://sir.kr/main/g5/)의 업데이트를 간소화 하기위한 플러그인 입니다.  

## 특징
skin폴더와 theme폴더는 업데이트 되지 않습니다.  
나름 PSR를 지켜 짜려고 했으나 개떡이 되었습니다.  
누구나 수정 배포가 가능하며 상업적이용이 가능합니다.

## 사용법
`html` 와 같은 위치에 파일을 넣고 `php updater.php`로 실행 시켜줍니다.

### 설정 방법
`updaterConfig.php`에 `__GNU_DIR__`에 `html`을 그누보드가 설치된 경로로 변경합니다.

### 스크립트
```shell
cd /var/www/
wget https://raw.githubusercontent.com/Silnex/gnuboard5-updater/master/updater.php
php updater.php update
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
- [x] updater cli 만들기
- [ ] 그누보드 관리자 UI만들기
- ETC . . .

## 잡설
나름 신경쓰면서 만들었는데 생각보다 코드가 드럽네요..
