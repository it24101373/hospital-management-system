@echo off
title Hospital Management System - Public Online Access
echo =========================================================================
echo       HOSPITAL MANAGEMENT SYSTEM - INSTANT PUBLIC ONLINE ACCESS
echo =========================================================================
echo.

cd /d "%~dp0"

:: Check if cloudflared exists
where cloudflared >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    set CLOUDFLARED_CMD=cloudflared
    goto START_CLOUDFLARE
)

if exist "cloudflared.exe" (
    set CLOUDFLARED_CMD=cloudflared.exe
    goto START_CLOUDFLARE
)

echo [1/2] Downloading standalone cloudflared.exe (one-time setup)...
curl.exe -L -o "cloudflared.exe" "https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe"

if exist "cloudflared.exe" (
    set CLOUDFLARED_CMD=cloudflared.exe
    goto START_CLOUDFLARE
)

:: Fallback if cloudflared cannot be downloaded: Use built-in Windows OpenSSH tunnel
goto START_SSH_TUNNEL

:START_CLOUDFLARE
echo.
echo Connecting with HTTP/2 protocol over IPv4 (bypassing UDP/QUIC ISP blocks)...
echo Look for your live link ending in ".trycloudflare.com" below:
echo =========================================================================
echo.
%CLOUDFLARED_CMD% tunnel --protocol http2 --edge-ip-version 4 --url http://localhost:8000

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo Cloudflare connection was blocked by your local network or ISP.
    echo Switching to backup SSH tunnel (localhost.run)...
    goto START_SSH_TUNNEL
)
goto END

:START_SSH_TUNNEL
echo.
echo =========================================================================
echo    STARTING DIRECT SECURE TUNNEL (Via Windows OpenSSH & localhost.run)
echo =========================================================================
echo Connecting to localhost.run on port 80/443...
echo Look for the public https://xxxx.lhr.life URL below:
echo.
ssh -o StrictHostKeyChecking=no -R 80:localhost:8000 nokey@localhost.run

:END
pause
