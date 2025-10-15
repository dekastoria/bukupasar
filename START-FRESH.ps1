# Bukupasar - Start Fresh Script
# Run this script to start dev servers cleanly

Write-Host "=== Bukupasar Development Server Startup ===" -ForegroundColor Cyan
Write-Host ""

# Step 1: Kill all Node processes
Write-Host "Step 1: Stopping all Node.js processes..." -ForegroundColor Yellow
Get-Process -Name node -ErrorAction SilentlyContinue | Stop-Process -Force
Start-Sleep -Seconds 2
Write-Host "✓ All Node processes stopped" -ForegroundColor Green
Write-Host ""

# Step 2: Clear Next.js cache
Write-Host "Step 2: Clearing Next.js cache..." -ForegroundColor Yellow
$frontendPath = "C:\laragon\www\bukupasar\bukupasar-frontend"
if (Test-Path "$frontendPath\.next") {
    Remove-Item -Path "$frontendPath\.next" -Recurse -Force
    Write-Host "✓ .next cache cleared" -ForegroundColor Green
} else {
    Write-Host "✓ No cache to clear" -ForegroundColor Green
}
Write-Host ""

# Step 3: Check backend
Write-Host "Step 3: Checking Laravel backend..." -ForegroundColor Yellow
$backendRunning = Test-NetConnection -ComputerName 127.0.0.1 -Port 8000 -InformationLevel Quiet
if ($backendRunning) {
    Write-Host "✓ Backend is running on http://127.0.0.1:8000" -ForegroundColor Green
} else {
    Write-Host "⚠ Backend is NOT running!" -ForegroundColor Red
    Write-Host "  Please start in another terminal:" -ForegroundColor Yellow
    Write-Host "  cd C:\laragon\www\bukupasar\bukupasar-backend" -ForegroundColor White
    Write-Host "  php artisan serve --host=127.0.0.1 --port=8000" -ForegroundColor White
}
Write-Host ""

# Step 4: Start Frontend
Write-Host "Step 4: Starting Next.js dev server..." -ForegroundColor Yellow
Write-Host "Location: $frontendPath" -ForegroundColor White
Write-Host ""
Write-Host "Starting npm run dev..." -ForegroundColor Cyan
Write-Host "-------------------------------------" -ForegroundColor Gray

Set-Location $frontendPath
npm run dev
