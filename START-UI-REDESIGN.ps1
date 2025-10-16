# Start UI Redesign Development Server

Write-Host "🎨 Starting Bukupasar UI Redesign Dev Server..." -ForegroundColor Cyan
Write-Host ""

# Stop any existing node processes
Write-Host "🛑 Stopping existing Node processes..." -ForegroundColor Yellow
Get-Process -Name "node" -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
Start-Sleep -Seconds 2

# Navigate to frontend
Set-Location "C:\laragon\www\bukupasar\bukupasar-frontend"

# Clear cache
Write-Host "🧹 Clearing Next.js cache..." -ForegroundColor Yellow
Remove-Item -Recurse -Force .next -ErrorAction SilentlyContinue
Start-Sleep -Seconds 1

# Start dev server
Write-Host ""
Write-Host "🚀 Starting dev server..." -ForegroundColor Green
Write-Host "   Local: http://localhost:3001" -ForegroundColor Cyan
Write-Host ""
Write-Host "Press Ctrl+C to stop" -ForegroundColor Yellow
Write-Host ""

npm run dev
