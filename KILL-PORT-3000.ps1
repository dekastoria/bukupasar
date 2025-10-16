# Kill all processes using port 3000

Write-Host "🔪 Killing all Node.js processes..." -ForegroundColor Yellow
Get-Process -Name "node" -ErrorAction SilentlyContinue | Stop-Process -Force

Write-Host "⏳ Waiting for ports to release..." -ForegroundColor Yellow
Start-Sleep -Seconds 3

Write-Host "🔍 Checking port 3000..." -ForegroundColor Cyan
$port3000 = netstat -ano | findstr :3000 | findstr LISTENING

if ($port3000) {
    Write-Host "⚠️  Port 3000 still in use:" -ForegroundColor Red
    Write-Host $port3000
    
    # Extract PID and kill it
    $port3000 | ForEach-Object {
        if ($_ -match '\s+(\d+)\s*$') {
            $pid = $matches[1]
            Write-Host "🔪 Killing process $pid..." -ForegroundColor Yellow
            Stop-Process -Id $pid -Force -ErrorAction SilentlyContinue
        }
    }
    
    Start-Sleep -Seconds 2
}

Write-Host ""
Write-Host "✅ Port 3000 should be free now!" -ForegroundColor Green
Write-Host ""
Write-Host "Run this to start dev server:" -ForegroundColor Cyan
Write-Host "  cd bukupasar-frontend" -ForegroundColor White
Write-Host "  npm run dev" -ForegroundColor White
