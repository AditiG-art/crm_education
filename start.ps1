Write-Host "Starting Smart Campus CRM on http://localhost:8000..." -ForegroundColor Green
$mysqlRunning = Get-Process mysqld -ErrorAction SilentlyContinue
if (-not $mysqlRunning) {
    Write-Host "Starting local MySQL engine on port 3307..." -ForegroundColor Yellow
    Start-Process -FilePath "C:\xampp\mysql\bin\mysqld.exe" -ArgumentList "--port=3307 --standalone" -WindowStyle Hidden
    Start-Sleep -Seconds 2
}
Write-Host "PHP Server running at http://localhost:8000" -ForegroundColor Cyan
& "C:\xampp\php\php.exe" -S localhost:8000 -t $PSScriptRoot
