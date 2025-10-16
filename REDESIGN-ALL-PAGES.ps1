# Script to redesign ALL pages with consistent sizes and emerald-only colors
# Replace all blue colors with emerald and standardize font sizes

$frontendPath = "C:\laragon\www\bukupasar\bukupasar-frontend\app\(authenticated)"

Write-Host "🎨 Starting comprehensive UI redesign..." -ForegroundColor Cyan
Write-Host ""

# Define replacements
$replacements = @{
    # Colors: Blue -> Emerald
    "sky-600" = "emerald-600"
    "sky-700" = "emerald-700"
    "sky-500" = "emerald-500"
    "sky-50" = "emerald-50"
    "sky-200" = "emerald-200"
    "blue-600" = "emerald-600"
    "blue-700" = "emerald-700"
    "blue-50" = "emerald-50"
    "blue-200" = "emerald-200"
    
    # Font sizes: Large -> Modern
    "text-3xl" = "text-xl"
    "text-2xl" = "text-lg"
    "text-xl" = "text-base"
    "text-lg" = "text-sm"
    
    # Button & Input heights
    "h-20" = "h-12"
    "h-14" = "h-9"
    "h-12" = "h-9"
    
    # Icon sizes
    "h-6 w-6" = "h-4 w-4"
    "h-5 w-5" = "h-4 w-4"
    
    # Spacing
    "gap-4" = "gap-3"
    "gap-6" = "gap-3"
    "space-y-6" = "space-y-4"
    "space-y-4" = "space-y-3"
    "p-6" = "p-4"
    "p-4" = "p-3"
    "py-2" = "py-1.5"
    
    # Step indicator
    "h-10 w-10" = "h-8 w-8"
    "h-1 w-12" = "h-0.5 w-10"
    
    # Green color for success (standardize)
    "bg-green-600" = "bg-emerald-600"
    "hover:bg-green-700" = "hover:bg-emerald-700"
    "text-green-600" = "text-emerald-600"
}

# Files to process
$files = @(
    "$frontendPath\pemasukan\tambah\page.tsx"
    "$frontendPath\pengeluaran\tambah\page.tsx"
    "$frontendPath\sewa\page.tsx"
    "$frontendPath\laporan\harian\page.tsx"
    "$frontendPath\laporan\ringkasan\page.tsx"
    "$frontendPath\laporan\layout.tsx"
)

foreach ($file in $files) {
    if (Test-Path $file) {
        Write-Host "📝 Processing: $($file.Split('\')[-3..-1] -join '\')" -ForegroundColor Yellow
        
        $content = Get-Content $file -Raw -Encoding UTF8
        $originalContent = $content
        
        foreach ($key in $replacements.Keys) {
            $content = $content -replace [regex]::Escape($key), $replacements[$key]
        }
        
        if ($content -ne $originalContent) {
            Set-Content $file -Value $content -Encoding UTF8 -NoNewline
            Write-Host "   ✅ Updated!" -ForegroundColor Green
        } else {
            Write-Host "   ⏭️  No changes needed" -ForegroundColor Gray
        }
    } else {
        Write-Host "   ⚠️  File not found: $file" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "✅ Redesign complete!" -ForegroundColor Green
Write-Host ""
Write-Host "Run these to verify:" -ForegroundColor Cyan
Write-Host "  cd bukupasar-frontend" -ForegroundColor White
Write-Host "  npx tsc --noEmit" -ForegroundColor White
Write-Host "  npm run build" -ForegroundColor White
