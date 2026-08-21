<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
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
            'https://images.unsplash.com/photo-1583337130417-3346a1be7dee?auto=format&fit=crop&w=900&q=82',
            'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?auto=format&fit=crop&w=900&q=82',
            'https://images.unsplash.com/photo-1529778873920-4da4926a72c2?auto=format&fit=crop&w=900&q=82',
            'https://images.unsplash.com/photo-1519052537078-e6302a4968d4?auto=format&fit=crop&w=900&q=82',
        ];
        $i = 0;
        foreach ($catalog as $categoryName => [$petType, $names]) {
            $category = Category::updateOrCreate(['slug'=>Str::slug($categoryName)], ['name'=>$categoryName,'description'=>'Phụ kiện được Pawora tuyển chọn cho những khoảnh khắc gần gũi mỗi ngày.','is_active'=>true]);
            foreach ($names as $name) {
                $price = 129000 + ($i * 27000);
                Product::updateOrCreate(['sku'=>'PAW-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT)], [
                    'category_id'=>$category->id,'name'=>$name,'slug'=>Str::slug($name),'pet_type'=>$petType,
                    'short_description'=>'Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.',
                    'description'=>'Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',
                    'price'=>$price,'compare_price'=>$i % 3 === 0 ? $price + 80000 : null,'stock'=>5 + ($i % 18),
                    'image'=>$images[$i % count($images)],'rating'=>4.5 + (($i % 5) / 10),'review_count'=>12 + $i,
                    'is_featured'=>$i < 8,'status'=>'active',
                ]);
                $i++;
            }
        }
        Coupon::updateOrCreate(['code'=>'PAWORA10'], ['type'=>'percent','value'=>10,'minimum_order'=>500000,'usage_limit'=>500,'is_active'=>true]);
    }
}
