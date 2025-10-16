# Start Fresh Development Environment

Write-Host "🔄 Starting Bukupasar Fresh..." -ForegroundColor Cyan
Write-Host ""

# Kill existing processes
Write-Host "🛑 Stopping existing processes..." -ForegroundColor Yellow
Get-Process -Name "node" -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
Get-Process -Name "php" | Where-Object {$_.CommandLine -like "*artisan serve*"} -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
Start-Sleep -Seconds 2

# Clear cache
Write-Host "🧹 Clearing cache..." -ForegroundColor Yellow
Set-Location "C:\laragon\www\bukupasar\bukupasar-frontend"
Remove-Item -Recurse -Force .next -ErrorAction SilentlyContinue
Start-Sleep -Seconds 1

# Start Backend in new window
Write-Host ""
Write-Host "🚀 Starting Backend..." -ForegroundColor Green
Set-Location "C:\laragon\www\bukupasar\bukupasar-backend"
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd C:\laragon\www\bukupasar\bukupasar-backend; php artisan serve --host=127.0.0.1 --port=8000"
Start-Sleep -Seconds 3

# Start Frontend in new window
Write-Host "🚀 Starting Frontend..." -ForegroundColor Green
Set-Location "C:\laragon\www\bukupasar\bukupasar-frontend"
Start-Process powershell -ArgumentList "-NoExit", "-Command", "cd C:\laragon\www\bukupasar\bukupasar-frontend; npm run dev"
Start-Sleep -Seconds 2

Write-Host ""
Write-Host "✅ Both servers starting..." -ForegroundColor Green
Write-Host ""
Write-Host "📍 URLs:" -ForegroundColor Cyan
Write-Host "   Backend:  http://127.0.0.1:8000" -ForegroundColor White
Write-Host "   Frontend: http://localhost:3000" -ForegroundColor White
Write-Host ""
Write-Host "⏱️  Wait 10-15 seconds for servers to be ready..." -ForegroundColor Yellow
Write-Host ""
Write-Host "Press any key to open browser..." -ForegroundColor Cyan
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")

Start-Process "http://localhost:3000"
