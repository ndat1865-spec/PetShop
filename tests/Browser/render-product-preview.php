<?php

// Render the real preview without an admin login or database changes.
// Usage: php tests/Browser/render-product-preview.php [public asset URL]
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$app['session']->start();

$catalog = require database_path('seeders/data/pawora_product_content.php');
$assetUrl = rtrim($argv[1] ?? 'http://localhost/PetShopnew/public', '/');
$product = new App\Models\Product(array_merge($catalog['PAW-0002'], [
    'name' => 'Dây thừng Tug & Joy',
    'image' => $assetUrl.'/media/products/pawora-clean/pawora-product-0002.png',
    'status' => 'active',
]));
$preview = view('admin._product-preview', ['product' => $product])->render();
$css = file_get_contents(resource_path('css/app.css')).file_get_contents(resource_path('css/product-content.css'));
$html = '<!doctype html><html lang="vi"><meta charset="utf-8"><style>'.$css.'</style><body class="admin-body"><main style="padding:80px 20px;display:flex;justify-content:flex-end"><div style="width:340px;max-width:100%">'.$preview.'</div></main></body></html>';
$directory = base_path('output/playwright');
if (! is_dir($directory)) {
    mkdir($directory, 0777, true);
}
file_put_contents($directory.'/product-preview.html', $html);
echo "Rendered admin preview: output/playwright/product-preview.html\n";
