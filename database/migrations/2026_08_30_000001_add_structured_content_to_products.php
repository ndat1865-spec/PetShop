<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('detail_heading', 160)->nullable()->after('description');
            $table->json('highlights')->nullable()->after('detail_heading');
            $table->json('specifications')->nullable()->after('highlights');
            $table->longText('care_instructions')->nullable()->after('specifications');
            $table->json('size_guide')->nullable()->after('care_instructions');
            $table->string('content_image', 1000)->nullable()->after('size_guide');
        });

        DB::table('products')
            ->where('slug', 'khan-choang-pawora')
            ->update([
                'short_description' => 'Khăn choàng Pawora là điểm nhấn gọn gàng cho những buổi dạo chơi, chụp ảnh hay ngày thường. Dáng tam giác dễ phối cùng nhiều màu lông và phong cách.',
                'detail_heading' => 'Một điểm nhấn nhỏ cho diện mạo thật riêng.',
                'description' => 'Thiết kế tam giác tạo cảm giác nhẹ nhàng, vừa đủ nổi bật mà không làm tổng thể trở nên cầu kỳ. Sản phẩm có nhiều lựa chọn màu sắc và kích thước để bạn dễ tìm phiên bản phù hợp cho bé.',
                'highlights' => json_encode([
                    'Dáng tam giác gọn gàng, dễ phối.',
                    'Có nhiều màu sắc và kích thước.',
                    'Phù hợp cho đi chơi, chụp ảnh và sử dụng hằng ngày.',
                    'Thiết kế dễ đeo và tháo.',
                ], JSON_UNESCAPED_UNICODE),
            ]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'detail_heading',
                'highlights',
                'specifications',
                'care_instructions',
                'size_guide',
                'content_image',
            ]);
        });
    }
};
