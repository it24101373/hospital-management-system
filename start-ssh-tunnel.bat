@echo off
title Hospital Management System - Instant Online Access (SSH Tunnel)
echo =========================================================================
echo       HOSPITAL MANAGEMENT SYSTEM - INSTANT ONLINE ACCESS
echo       (Bypasses all ISP and Firewall UDP/QUIC blocks)
echo =========================================================================
echo.
echo Connecting local hospital server to the internet via localhost.run...
echo You will see an HTTPS link below (e.g. https://xxxx.lhr.life)
echo.
ssh -o StrictHostKeyChecking=no -R 80:localhost:8000 nokey@localhost.run
pause
