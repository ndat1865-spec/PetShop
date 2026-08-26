<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(['email'=>'admin@pawora.vn'], ['name'=>'Pawora Admin','phone'=>'0909000001','role'=>'admin','password'=>Hash::make('Pawora@2026')]);
        User::updateOrCreate(['email'=>'khach@pawora.vn'], ['name'=>'Nguyễn An','phone'=>'0909000002','role'=>'customer','password'=>Hash::make('Pawora@2026'),'province'=>'Thành phố Hà Nội','ward'=>'Phường Tây Tựu','address_line'=>'Số 18, ngõ 5 Ngọa Long']);
        $catalog = [
            'Đồ chơi cho chó' => ['dog', ['Bóng cao su Pounce','Dây thừng Tug & Joy','Đĩa bay Flexi Disc','Đồ chơi phát tiếng Nibble']],
            'Đồ chơi cho mèo' => ['cat', ['Cần câu lông vũ Miso','Bóng len Moon Ball','Bàn cào móng Cloud','Đường hầm Hide & Seek']],
            'Vòng cổ & dây dắt' => ['all', ['Vòng cổ AirTag Linen','Dây dắt Urban Walk','Yếm ngực Comfort Pro','Bộ dây dắt Weekend']],
            'Giường & chăn' => ['all', ['Giường ngủ Cocoon','Nệm mây Soft Nest','Chăn cotton Calm','Nhà vải Hideaway']],
            'Bát ăn & bình nước' => ['all', ['Bát sứ Slow Dining','Bình nước Travel Sip','Bát đôi Oak Stand','Máy nước Fresh Flow']],
            'Grooming' => ['all', ['Lược chải Easy Coat','Kềm cắt móng Safe Trim','Găng tay Gentle Groom','Khăn tắm Quick Dry']],
            'Quần áo thú cưng' => ['all', ['Áo thun Everyday','Áo mưa City Walk','Khăn choàng Pawora','Áo len Warm Hug']],
            'Phụ kiện du lịch' => ['all', ['Túi vận chuyển Go Together','Đệm xe Road Trip','Túi đựng snack Walkie','Bình nước Pocket Sip']],
        ];
        $images = [
            '/media/products/paw-0001.jpg', '/media/products/paw-0002.jpg',
            '/media/products/paw-0003.jpg', '/media/products/paw-0004.jpg',
            '/media/products/paw-0005.jpg', '/media/products/paw-0006.jpg',
            '/media/products/paw-0007.jpg', '/media/products/paw-0008.jpg',
            '/media/products/paw-0009.jpg', '/media/products/paw-0010.jpg',
            '/media/products/paw-0011.jpg', '/media/products/paw-0012.jpg',
            '/media/products/paw-0013.jpg', '/media/products/paw-0014.jpg',
            '/media/products/paw-0015.jpg', '/media/products/paw-0016.jpg',
            '/media/products/paw-0017.webp', '/media/products/paw-0018.webp',
            '/media/products/paw-0019.jpg', '/media/products/paw-0020.png',
            '/media/products/paw-0021.jpg', '/media/products/paw-0022.jpg',
            '/media/products/paw-0023.jpg', '/media/products/paw-0024.jpg',
            '/media/products/paw-0025.jpg', '/media/products/paw-0026.jpg',
            '/media/products/paw-0027.jpg', '/media/products/paw-0028.jpg',
            '/media/products/paw-0029.jpg', '/media/products/paw-0030.jpg',
            '/media/products/paw-0031.png', '/media/products/paw-0032.jpg',
        ];
        $optionSets = [
            'Đồ chơi cho chó' => [['Xanh lá', 'Vàng', 'Cam'], ['Nhỏ', 'Lớn']],
            'Đồ chơi cho mèo' => [['Hồng', 'Xanh dương', 'Tím'], ['Nhỏ', 'Lớn']],
            'Vòng cổ & dây dắt' => [['Be', 'Đen', 'Xanh navy'], ['S', 'M']],
            'Giường & chăn' => [['Kem', 'Xám', 'Hồng'], ['S', 'M']],
            'Bát ăn & bình nước' => [['Trắng', 'Xanh mint', 'Hồng'], ['350 ml', '550 ml']],
            'Grooming' => [['Trắng', 'Xanh dương', 'Hồng'], ['Tiêu chuẩn', 'Lớn']],
            'Quần áo thú cưng' => [['Kem', 'Xanh navy', 'Đỏ'], ['S', 'M']],
            'Phụ kiện du lịch' => [['Be', 'Đen', 'Xanh rêu'], ['Tiêu chuẩn', 'Lớn']],
        ];
        $i = 0;
        foreach ($catalog as $categoryName => [$petType, $names]) {
            $category = Category::updateOrCreate(['slug'=>Str::slug($categoryName)], ['name'=>$categoryName,'description'=>'Phụ kiện được Pawora tuyển chọn cho những khoảnh khắc gần gũi mỗi ngày.','is_active'=>true]);
            foreach ($names as $name) {
                $price = 129000 + ($i * 27000);
                $product = Product::updateOrCreate(['sku'=>'PAW-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT)], [
                    'category_id'=>$category->id,'name'=>$name,'slug'=>Str::slug($name),'pet_type'=>$petType,
                    'short_description'=>'Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.',
                    'description'=>'Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',
                    'price'=>$price,'compare_price'=>$i % 3 === 0 ? $price + 80000 : null,'stock'=>5 + ($i % 18),
                    'image'=>$images[$i % count($images)],'rating'=>4.5 + (($i % 5) / 10),'review_count'=>12 + $i,
                    'is_featured'=>$i < 8,'status'=>'active',
                ]);
                [$colors, $sizes] = $optionSets[$categoryName];
                foreach ($colors as $colorIndex => $color) {
                    foreach ($sizes as $sizeIndex => $size) {
                        ProductVariant::updateOrCreate(
                            ['sku' => $product->sku.'-'.($colorIndex + 1).'-'.($sizeIndex + 1)],
                            [
                                'product_id' => $product->id,
                                'name' => $color.' · '.$size,
                                'color' => $color,
                                'size' => $size,
                                'price' => $price + ($sizeIndex * 30000),
                                'stock' => 3 + (($i + $colorIndex + $sizeIndex) % 5),
                                'is_active' => true,
                            ]
                        );
                    }
                }
                $product->update(['stock' => $product->variants()->where('is_active', true)->sum('stock')]);
                $i++;
            }
        }
        Coupon::updateOrCreate(['code'=>'PAWORA10'], ['type'=>'percent','value'=>10,'minimum_order'=>500000,'usage_limit'=>500,'is_active'=>true]);
    }
}
