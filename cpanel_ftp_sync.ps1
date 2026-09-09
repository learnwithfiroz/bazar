# cPanel 1-Click Package Builder (PowerShell)
$ErrorActionPreference = "SilentlyContinue"

Write-Host "=================================================================" -ForegroundColor Cyan
Write-Host " Building cPanel Ready Update Package..." -ForegroundColor Green
Write-Host "=================================================================" -ForegroundColor Cyan

# 1. Clean local caches before deploying
Remove-Item -Path "e:\Principal\bootstrap\cache\*.php" -Force
Remove-Item -Path "e:\Principal\storage\framework\views\*.php" -Force

# 2. Prepare Clean Production Zip
$zipPath = "e:\Principal\cpanel_update_ready.zip"
if (Test-Path $zipPath) { Remove-Item $zipPath -Force }

$excludeItems = @(".git", ".github", "node_modules", "tests", "scratch", "cpanel_update_ready.zip", "database.sqlite")
$filesToZip = Get-ChildItem -Path "e:\Principal\*" -Exclude $excludeItems

Compress-Archive -Path $filesToZip.FullName -DestinationPath $zipPath -Force

$zipSize = [math]::Round((Get-Item $zipPath).Length / 1MB, 2)
Write-Host "ZIP Created Successfully: e:\Principal\cpanel_update_ready.zip ($zipSize MB)" -ForegroundColor Green
Write-Host "Done!" -ForegroundColor Cyan
