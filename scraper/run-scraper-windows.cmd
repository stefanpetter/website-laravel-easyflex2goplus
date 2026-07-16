@echo off
setlocal
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0run-scraper-windows.ps1" %*
exit /b %ERRORLEVEL%
