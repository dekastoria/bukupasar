<?php

$files = [
    'app/Filament/Resources/UserResource.php',
    'app/Filament/Resources/TransactionResource.php',
    'app/Filament/Resources/PaymentResource.php',
];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    
    if (!file_exists($path)) {
        echo "❌ File not found: $file\n";
        continue;
    }
    
    $content = file_get_contents($path);
    $original = $content;
    
    // Add import if not exists
    if (strpos($content, 'use Filament\Actions;') === false) {
        // Find the position after "use BackedEnum;" or "use Filament\Forms;"
        $content = preg_replace(
            '/(use BackedEnum;\n)/m',
            '$1use Filament\Actions;' . "\n",
            $content,
            1
        );
    }
    
    // Replace Tables\Actions\ with Actions\
    $content = str_replace('Tables\Actions\EditAction', 'Actions\EditAction', $content);
    $content = str_replace('Tables\Actions\DeleteAction', 'Actions\DeleteAction', $content);
    $content = str_replace('Tables\Actions\BulkActionGroup', 'Actions\BulkActionGroup', $content);
    $content = str_replace('Tables\Actions\DeleteBulkAction', 'Actions\DeleteBulkAction', $content);
    
    if ($content !== $original) {
        file_put_contents($path, $content);
        echo "✅ Fixed: $file\n";
    } else {
        echo "⏭️  No changes needed: $file\n";
    }
}

echo "\n✅ All files processed!\n";
