@echo off
title cPanel Auto Deploy & Update Tool
color 0A
echo =================================================================
echo  🚀 cPanel 1-Click Auto Update Package Builder
echo  Target: https://principal.firoz-ahmed.com
echo =================================================================
echo.
echo [1/2] Cleaning local caches...
del /q "e:\Principal\bootstrap\cache\*.php" 2>nul
del /q "e:\Principal\storage\framework\views\*.php" 2>nul

echo [2/2] Generating latest clean ZIP package...
powershell -ExecutionPolicy Bypass -File "e:\Principal\cpanel_ftp_sync.ps1"

echo.
echo =================================================================
echo  DONE! Double click this anytime you make changes to your code.
echo =================================================================
pause
