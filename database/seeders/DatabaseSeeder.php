<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ChatAutoReplyRule;
use App\Models\ChatConversation;
use App\Models\ContactMessage;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
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
        $images = array_map(
            fn (int $number) => sprintf(
                '/media/products/pawora-clean/pawora-product-%04d.%s',
                $number,
                $number <= 8 ? 'png' : 'jpg'
            ),
            range(1, 32)
        );
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
                    'image'=>$images[$i % count($images)],
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
        $demoCoupons = [
            ['code' => 'PAWORA10', 'type' => 'percent', 'value' => 10, 'minimum_order' => 500000, 'usage_limit' => 500],
            ['code' => 'BEMOI50', 'type' => 'fixed', 'value' => 50000, 'minimum_order' => 300000, 'usage_limit' => 300],
            ['code' => 'PAWORA15', 'type' => 'percent', 'value' => 15, 'minimum_order' => 800000, 'usage_limit' => 200],
            ['code' => 'GIAM100K', 'type' => 'fixed', 'value' => 100000, 'minimum_order' => 1200000, 'usage_limit' => 100],
        ];
        foreach ($demoCoupons as $coupon) {
            Coupon::firstOrCreate(['code' => $coupon['code']], [...$coupon, 'is_active' => true]);
        }

        $this->seedVerifiedReviews();
        $this->seedContactMessages();
        $this->seedChatSupport();
        $this->syncProductRatings();
    }

    private function seedVerifiedReviews(): void
    {
        $admin = User::where('email', 'admin@pawora.vn')->firstOrFail();
        $customerProfiles = [
            ['Trần Minh Anh', 'minhanh.demo@pawora.vn', '0911000101'],
            ['Lê Hoàng Nam', 'hoangnam.demo@pawora.vn', '0911000102'],
            ['Phạm Thu Trang', 'thutrang.demo@pawora.vn', '0911000103'],
            ['Nguyễn Đức Huy', 'duchuy.demo@pawora.vn', '0911000104'],
            ['Đỗ Khánh Linh', 'khanhlinh.demo@pawora.vn', '0911000105'],
            ['Vũ Quang Minh', 'quangminh.demo@pawora.vn', '0911000106'],
            ['Bùi Ngọc Mai', 'ngocmai.demo@pawora.vn', '0911000107'],
            ['Hoàng Gia Bảo', 'giabao.demo@pawora.vn', '0911000108'],
        ];
        $titles = [
            'Bé nhà mình rất thích', 'Chất liệu chắc chắn', 'Đúng mô tả và dễ dùng', 'Thiết kế đẹp, đóng gói kỹ',
            'Phù hợp dùng hằng ngày', 'Kích thước vừa vặn', 'Trải nghiệm ở mức khá', 'Dễ vệ sinh hơn mình nghĩ',
        ];
        $comments = [
            'Sản phẩm hoàn thiện tốt, bề mặt dễ vệ sinh và bé nhà mình làm quen rất nhanh sau khi mở hộp.',
            'Mình đã dùng hơn hai tuần, đường may và các chi tiết vẫn chắc chắn, màu sắc giống hình trên trang.',
            'Kích thước đúng với phần mô tả, thao tác sử dụng đơn giản và không gây khó chịu cho thú cưng.',
            'Pawora đóng gói cẩn thận, sản phẩm không có mùi lạ và có hướng dẫn bảo quản khá rõ ràng.',
            'Thiết kế gọn, đặt trong nhà không chiếm nhiều diện tích và chất liệu cho cảm giác bền khi sử dụng.',
            'Mình chọn theo bảng kích thước và nhận được sản phẩm vừa vặn, bé vận động vẫn khá thoải mái.',
            'Chất lượng nhìn chung ổn nhưng màu thực tế trầm hơn ảnh một chút, phần còn lại đúng như mô tả.',
            'Sau vài lần vệ sinh sản phẩm vẫn giữ được dáng, đây là điểm mình hài lòng nhất khi sử dụng.',
        ];
        $ratings = [5, 4, 5, 4, 5, 3, 4, 2];
        $products = Product::active()->orderBy('id')->take(16)->get();

        foreach ($customerProfiles as $customerIndex => [$name, $email, $phone]) {
            $customer = User::updateOrCreate(['email' => $email], [
                'name' => $name,
                'phone' => $phone,
                'role' => User::ROLE_CUSTOMER,
                'is_active' => true,
                'password' => Hash::make('Pawora@2026'),
                'province' => $customerIndex % 2 ? 'Thành phố Hồ Chí Minh' : 'Thành phố Hà Nội',
                'ward' => $customerIndex % 2 ? 'Phường Bến Thành' : 'Phường Tây Tựu',
                'address_line' => (18 + $customerIndex).', đường Pawora',
            ]);
            $selectedProducts = collect(range(0, 2))->map(fn (int $offset) => $products[($customerIndex + ($offset * 4)) % $products->count()]);
            $order = Order::updateOrCreate(['number' => 'PAW-DEMO-RV-'.str_pad((string) ($customerIndex + 1), 2, '0', STR_PAD_LEFT)], [
                'user_id' => $customer->id,
                'customer_name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'shipping_address' => $customer->address_line.', '.$customer->ward.', '.$customer->province,
                'subtotal' => (int) $selectedProducts->sum(fn (Product $product) => (int) $product->price),
                'discount' => 0,
                'shipping_fee' => 0,
                'total' => (int) $selectedProducts->sum(fn (Product $product) => (int) $product->price),
                'currency' => 'VND',
                'payment_method' => 'cod',
                'payment_status' => 'paid',
                'paid_at' => now()->subDays(120 + $customerIndex),
                'status' => 'delivered',
            ]);
            $order->forceFill(['created_at' => now()->subDays(124 + $customerIndex), 'updated_at' => now()->subDays(118 + $customerIndex)])->saveQuietly();

            foreach ($selectedProducts as $offset => $product) {
                $order->items()->updateOrCreate(['product_id' => $product->id], [
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'unit_price' => $product->price,
                    'quantity' => 1,
                    'line_total' => $product->price,
                ]);
                $reviewIndex = ($customerIndex * 3) + $offset;
                $status = match ($reviewIndex) {
                    5, 17, 21 => 'pending',
                    11, 19 => 'hidden',
                    23 => 'rejected',
                    default => 'approved',
                };
                $contentIndex = ($customerIndex + $offset) % count($comments);
                $review = ProductReview::updateOrCreate([
                    'product_id' => $product->id,
                    'user_id' => $customer->id,
                ], [
                    'order_id' => $order->id,
                    'rating' => $ratings[$contentIndex],
                    'title' => $titles[$contentIndex],
                    'comment' => $comments[$contentIndex],
                    'status' => $status,
                    'admin_note' => $status === 'hidden' ? 'Tạm ẩn để kiểm tra thông tin mô tả.' : ($status === 'rejected' ? 'Nội dung chưa đủ thông tin về trải nghiệm sản phẩm.' : null),
                    'moderated_by' => $status === 'pending' ? null : $admin->id,
                    'moderated_at' => $status === 'pending' ? null : now()->subDays(60 + $reviewIndex),
                ]);
                $review->forceFill(['created_at' => now()->subDays(85 - $reviewIndex), 'updated_at' => now()->subDays(84 - $reviewIndex)])->saveQuietly();
            }
        }
    }

    private function seedContactMessages(): void
    {
        $samples = [
            ['Lan Anh', 'lananh.contact@pawora.vn', 'order', 'Mình muốn kiểm tra lại thời gian giao dự kiến của đơn hàng vì tuần tới gia đình có lịch đi xa.', 'new'],
            ['Minh Khoa', 'minhkhoa.contact@pawora.vn', 'product', 'Cho mình xin tư vấn kích thước phù hợp cho bé cún nặng khoảng tám ký và vòng ngực bốn mươi lăm centimet.', 'in_progress'],
            ['Thu Hà', 'thuha.contact@pawora.vn', 'returns', 'Sản phẩm mình nhận còn nguyên nhưng kích thước chưa phù hợp, nhờ Pawora hướng dẫn quy trình đổi sang size khác.', 'resolved'],
            ['Quốc Bảo', 'quocbao.contact@pawora.vn', 'shipping', 'Mã vận đơn đã được tạo nhưng hai ngày chưa cập nhật hành trình, nhờ đội ngũ kiểm tra giúp mình.', 'new'],
            ['Mai Phương', 'maiphuong.contact@pawora.vn', 'payment', 'Mình đã thanh toán qua ngân hàng nhưng trạng thái đơn vẫn đang chờ, vui lòng hỗ trợ đối soát giao dịch.', 'new'],
            ['Demo Spam', 'spam.contact@pawora.vn', 'other', 'Nội dung quảng cáo không liên quan đến sản phẩm hoặc dịch vụ chăm sóc khách hàng của Pawora.', 'spam'],
        ];

        foreach ($samples as $index => [$name, $email, $topic, $message, $status]) {
            $contact = ContactMessage::firstOrCreate(['email' => $email, 'topic' => $topic, 'message' => $message], [
                'name' => $name,
                'phone' => '0922000'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'status' => $status,
                'admin_note' => $status === 'resolved' ? 'Đã hướng dẫn khách đổi kích thước.' : null,
                'resolved_at' => $status === 'resolved' ? now()->subDays(3) : null,
            ]);
            $contact->forceFill(['created_at' => now()->subDays(6 - $index), 'updated_at' => now()->subDays(5 - $index)])->saveQuietly();
        }
    }

    private function seedChatSupport(): void
    {
        $admin = User::where('email', 'admin@pawora.vn')->firstOrFail();
        $rules = [
            ['Lời chào', ['xin chào', 'hello', 'chào shop'], 'contains', 'Xin chào! Pawora đã nhận được tin nhắn. Bạn cần hỗ trợ sản phẩm hay đơn hàng ạ?', 100, 'always'],
            ['Phí giao hàng', ['phí ship', 'phí vận chuyển', 'giao hàng'], 'contains', 'Pawora tính phí theo khu vực và sẽ hiển thị chính xác ở bước thanh toán. Đơn đủ điều kiện sẽ được miễn phí vận chuyển.', 90, 'always'],
            ['Đổi trả và hoàn tiền', ['đổi trả', 'hoàn tiền', 'trả hàng'], 'contains', 'Pawora hỗ trợ yêu cầu đổi trả trong vòng 7 ngày kể từ khi nhận hàng. Bạn gửi giúp mã đơn và tình trạng sản phẩm nhé.', 85, 'always'],
            ['Kiểm tra đơn hàng', ['mã vận đơn', 'đơn hàng của tôi', 'kiểm tra đơn'], 'contains', 'Bạn gửi mã đơn hàng để Pawora kiểm tra hành trình và mã vận đơn chính xác giúp bạn nhé.', 95, 'always'],
            ['Tư vấn sản phẩm', ['tư vấn sản phẩm', 'chọn thức ăn', 'chọn size'], 'contains', 'Bạn cho Pawora biết loại thú cưng, cân nặng và nhu cầu sử dụng. Nhân viên sẽ tư vấn lựa chọn phù hợp nhất.', 80, 'always'],
            ['Ngoài giờ hỗ trợ', ['hỗ trợ', 'nhân viên', 'tư vấn'], 'contains', 'Pawora đã ghi nhận yêu cầu ngoài giờ. Đội ngũ sẽ phản hồi từ 08:00 đến 18:00 trong ngày làm việc gần nhất.', 30, 'outside_hours'],
        ];

        foreach ($rules as $index => [$name, $keywords, $matchType, $response, $priority, $timeMode]) {
            ChatAutoReplyRule::updateOrCreate(['name' => $name], [
                'keywords' => $keywords,
                'match_type' => $matchType,
                'response' => $response,
                'priority' => $priority,
                'delay_seconds' => $index % 3,
                'once_per_conversation' => true,
                'active' => true,
                'working_time_mode' => $timeMode,
                'start_time' => $timeMode === 'always' ? null : '08:00:00',
                'end_time' => $timeMode === 'always' ? null : '18:00:00',
                'weekdays' => $timeMode === 'always' ? null : [1, 2, 3, 4, 5, 6],
                'stop_after_match' => true,
                'trigger_count' => 4 + $index,
                'created_by' => $admin->id,
                'updated_by' => $admin->id,
            ]);
        }

        $samples = [
            ['Trần Minh Anh', 'minhanh.demo@pawora.vn', '0911000101', 'PAW-DEMO-RV-01', 'new', 'high', 'Mình muốn kiểm tra đơn hàng của tôi đã có mã vận đơn chưa?', null],
            ['Lê Hoàng Nam', 'hoangnam.demo@pawora.vn', '0911000102', 'PAW-DEMO-RV-02', 'open', 'normal', 'Pawora tư vấn giúp mình chọn size yếm cho bé 9kg nhé.', 'Mình đã nhận thông tin. Bạn đo thêm vòng ngực của bé giúp Pawora nhé.'],
            ['Phạm Thu Trang', 'thutrang.demo@pawora.vn', '0911000103', 'PAW-DEMO-RV-03', 'waiting_customer', 'normal', 'Sản phẩm có đổi sang màu khác được không ạ?', 'Được bạn nhé. Bạn cho Pawora biết màu muốn đổi và tình trạng sản phẩm hiện tại.'],
            ['Nguyễn Đức Huy', 'duchuy.demo@pawora.vn', '0911000104', 'PAW-DEMO-RV-04', 'resolved', 'normal', 'Mình cần hướng dẫn vệ sinh giường cho thú cưng.', 'Pawora đã gửi hướng dẫn bảo quản. Bạn có thể giặt phần vỏ ở chế độ nhẹ và phơi nơi thoáng mát.'],
            ['Đỗ Khánh Linh', 'khanhlinh.demo@pawora.vn', '0911000105', 'PAW-DEMO-RV-05', 'new', 'urgent', 'Mình đã thanh toán nhưng đơn vẫn hiện đang chờ.', null],
            ['Vũ Quang Minh', 'quangminh.demo@pawora.vn', '0911000106', 'PAW-DEMO-RV-06', 'open', 'high', 'Mã vận đơn hai ngày rồi chưa cập nhật hành trình.', 'Pawora đang đối soát với đơn vị vận chuyển và sẽ cập nhật lại cho bạn sớm nhất.'],
            ['Bùi Ngọc Mai', 'ngocmai.demo@pawora.vn', '0911000107', 'PAW-DEMO-RV-07', 'closed', 'normal', 'Cảm ơn shop, mình đã nhận được hàng rồi.', 'Cảm ơn bạn đã tin chọn Pawora. Chúc bé có thật nhiều trải nghiệm vui vẻ!'],
            ['Hoàng Gia Bảo', 'giabao.demo@pawora.vn', '0911000108', 'PAW-DEMO-RV-08', 'new', 'normal', 'Phí ship về Hà Nội hiện tại là bao nhiêu?', null],
            ['Nguyễn Hải Yến', 'haiyen.chat@pawora.vn', '0938111222', null, 'waiting_customer', 'normal', 'Mình muốn hỏi về chính sách hoàn tiền.', 'Bạn gửi giúp Pawora mã đơn và ảnh tình trạng sản phẩm để đội ngũ kiểm tra nhé.'],
            ['Demo Spam', 'spam.chat@pawora.vn', '0900000999', null, 'spam', 'normal', 'Nội dung quảng cáo không liên quan.', null],
        ];

        foreach ($samples as $index => [$name, $email, $phone, $orderNumber, $status, $priority, $customerMessage, $adminReply]) {
            $customer = User::where('email', $email)->first();
            $conversation = ChatConversation::updateOrCreate(['guest_email' => $email], [
                'uuid' => ChatConversation::where('guest_email', $email)->value('uuid') ?: (string) Str::uuid(),
                'channel_key' => hash('sha256', 'pawora-demo-chat-channel-'.$index),
                'access_token_hash' => hash('sha256', 'pawora-demo-chat-token-'.$index),
                'user_id' => $customer?->id,
                'guest_name' => $name,
                'guest_phone' => $phone,
                'order_number' => $orderNumber,
                'assigned_to' => in_array($status, ['open', 'waiting_customer', 'resolved', 'closed'], true) ? $admin->id : null,
                'status' => $status,
                'priority' => $priority,
                'bot_enabled' => $adminReply === null,
                'customer_unread_count' => $adminReply ? 1 : 0,
                'admin_unread_count' => in_array($status, ['new', 'open'], true) ? 1 : 0,
                'last_message_at' => now()->subHours(10 - $index),
                'closed_at' => in_array($status, ['closed', 'spam'], true) ? now()->subHours(2) : null,
            ]);
            $customerEntry = $conversation->messages()->firstOrCreate(['sender_type' => 'customer', 'content' => $customerMessage], [
                'client_message_id' => 'pawora-demo-customer-'.$index,
                'sender_id' => $customer?->id,
                'message_type' => 'text',
            ]);
            $customerEntry->forceFill(['created_at' => now()->subHours(12 - $index), 'updated_at' => now()->subHours(12 - $index)])->saveQuietly();
            if ($index === 0 || $index === 7) {
                $rule = ChatAutoReplyRule::where('name', $index === 0 ? 'Kiểm tra đơn hàng' : 'Phí giao hàng')->first();
                $botText = $rule?->response;
                if ($botText) {
                    $conversation->messages()->firstOrCreate(['sender_type' => 'bot', 'content' => $botText], [
                        'auto_reply_rule_id' => $rule->id,
                        'message_type' => 'text',
                    ]);
                }
            }
            if ($adminReply) {
                $conversation->messages()->firstOrCreate(['sender_type' => 'admin', 'content' => $adminReply], [
                    'sender_id' => $admin->id,
                    'message_type' => 'text',
                ]);
            }
        }
    }

    private function syncProductRatings(): void
    {
        Product::query()->update(['rating' => 0, 'review_count' => 0]);
        ProductReview::approved()
            ->selectRaw('product_id, COUNT(*) as total, AVG(rating) as average_rating')
            ->groupBy('product_id')
            ->get()
            ->each(fn ($summary) => Product::whereKey($summary->product_id)->update([
                'rating' => round((float) $summary->average_rating, 1),
                'review_count' => (int) $summary->total,
            ]));
    }
}
