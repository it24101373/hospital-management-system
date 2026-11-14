@echo off
title Hospital Management System (HMS) - Apex Memorial Hospital
echo =========================================================================
echo               HOSPITAL MANAGEMENT SYSTEM (HMS)
echo                  Apex Memorial Hospital Portal
echo =========================================================================
echo.
echo Starting built-in PHP web server on http://localhost:8000 ...
echo Database: SQLite (Auto-initialized with sample clinical records)
echo Press Ctrl+C in this terminal window to stop the server at any time.
echo.

set PHP_BIN="C:\xampp\php\php.exe"
if not exist %PHP_BIN% (
    set PHP_BIN=php
)

start "" http://localhost:8000
%PHP_BIN% -S localhost:8000 -t "%~dp0"
pause
