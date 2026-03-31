@echo off
setlocal

set "ROOT_DIR=%~dp0"
set "APP_DIR=%ROOT_DIR%kaynak"
set "PORT=%~1"

if "%PORT%"=="" set "PORT=9100"

if not exist "%APP_DIR%\artisan" (
    echo Uygulama klasoru bulunamadi: "%APP_DIR%"
    exit /b 1
)

pushd "%APP_DIR%"

where php >nul 2>nul
if errorlevel 1 (
    echo PHP bulunamadi. PATH'e ekli oldugundan emin ol.
    popd
    exit /b 1
)

where docker >nul 2>nul
if errorlevel 1 (
    echo Docker bulunamadi. Docker Desktop'i acip tekrar dene.
    popd
    exit /b 1
)

echo [1/4] PostgreSQL servisi baslatiliyor...
docker compose up -d
if errorlevel 1 (
    popd
    exit /b 1
)

echo [2/4] Veritabani migrasyonlari ve seed calisiyor...
php artisan migrate --seed --force
if errorlevel 1 (
    popd
    exit /b 1
)

if not exist "public\storage" (
    echo [3/4] storage link olusturuluyor...
    php artisan storage:link >nul 2>nul
) else (
    echo [3/4] storage link zaten mevcut.
)

echo [4/4] Uygulama baslatiliyor: http://localhost:%PORT%
echo Cikmak icin Ctrl+C kullan.
php -S localhost:%PORT% -t public server-router.php

set "EXIT_CODE=%ERRORLEVEL%"
popd
exit /b %EXIT_CODE%
