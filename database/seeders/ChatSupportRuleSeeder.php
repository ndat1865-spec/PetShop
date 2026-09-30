<?php

namespace Database\Seeders;

use App\Models\ChatAutoReplyRule;
use Illuminate\Database\Seeder;

class ChatSupportRuleSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            ['greeting', 'Lời chào', 'general', ['xin chào', 'hello', 'chào shop'], 'Chào bạn! Bạn cần tìm sản phẩm, xem voucher hay hỗ trợ đơn hàng? Bạn cũng có thể chọn Yêu cầu nhân viên.', 10, false],
            ['buying', 'Hướng dẫn đặt hàng', 'shopping', ['cách mua', 'đặt hàng thế nào', 'hướng dẫn mua', 'mua hàng'], 'Bạn chọn sản phẩm và biến thể, thêm vào giỏ rồi đăng nhập để thanh toán. Giá, phí ship và voucher được kiểm tra ở bước thanh toán.', 180, false],
            ['discovery', 'Tìm sản phẩm phù hợp', 'shopping', ['tư vấn sản phẩm', 'sản phẩm cho chó', 'sản phẩm cho mèo', 'ngân sách', 'tìm sản phẩm'], 'Bạn cho Pawora biết bé là chó hay mèo, nhu cầu sử dụng và ngân sách dự kiến nhé. Xem danh mục sản phẩm hoặc chọn Yêu cầu nhân viên để được tư vấn cụ thể.', 200, false],
            ['size', 'Chọn size và màu', 'shopping', ['chọn size', 'kích thước', 'chọn màu', 'đổi màu', 'biến thể'], 'Bạn mở trang sản phẩm để xem các size/màu hiện có. Gửi tên sản phẩm và số đo phù hợp (ví dụ vòng ngực với yếm), nhân viên sẽ hỗ trợ chọn size; không chỉ dựa vào cân nặng.', 220, false],
            ['stock', 'Giá và tồn kho', 'shopping', ['còn hàng', 'hết hàng', 'giá bao nhiêu', 'tồn kho', 'khi nào có hàng'], 'Giá và tồn kho phụ thuộc sản phẩm và biến thể đang chọn. Bạn gửi tên hoặc đường dẫn sản phẩm để nhân viên kiểm tra; Pawora chưa thể xác nhận ngày nhập lại qua câu trả lời tự động.', 230, false],
            ['voucher', 'Xem mã giảm giá', 'voucher', ['mã giảm giá', 'voucher', 'khuyến mãi', 'mã ưu đãi', 'coupon', 'giảm giá'], 'Bạn đăng nhập rồi mở Ví voucher trong tài khoản để xem voucher hiện có. Khi thanh toán, hệ thống kiểm tra hạn dùng, giá trị đơn tối thiểu và lượt sử dụng. Pawora không tự tạo mã ngoài danh sách đang có.', 300, false],
            ['voucher_error', 'Voucher không áp dụng được', 'voucher', ['không dùng được mã', 'không áp dụng được', 'mã không hợp lệ', 'voucher lỗi', 'mã hết hạn'], 'Bạn kiểm tra hạn dùng, giá trị đơn tối thiểu và giới hạn sử dụng trong Ví voucher. Nếu vẫn lỗi, gửi tên mã và thông báo tại checkout, hoặc chọn Yêu cầu nhân viên. Không cần gửi mật khẩu hay thông tin thẻ.', 400, false],
            ['voucher_usage', 'Cách dùng voucher', 'voucher', ['cách dùng mã', 'dùng voucher', 'sử dụng voucher', 'nhập mã giảm', 'áp dụng mã giảm', 'cách dùng coupon'], 'Ở trang thanh toán, bấm Chọn hoặc nhập mã, chọn voucher hoặc nhập mã của bạn rồi bấm Áp dụng. Kiểm tra phần giảm giá và tổng tiền trước khi đặt hàng. Nếu không dùng được, gửi tên mã và thông báo lỗi để Pawora hướng dẫn tiếp.', 380, false],
            ['shipping', 'Phí giao hàng', 'shipping', ['phí ship', 'phí vận chuyển', 'miễn phí ship', 'freeship', 'khu vực giao'], 'Pawora tính phí theo địa chỉ và giá trị đơn. Bạn chọn địa chỉ ở bước thanh toán để xem phí chính xác và điều kiện miễn phí vận chuyển đang áp dụng.', 250, false],
            ['delivery_eta', 'Thời gian giao dự kiến', 'shipping', ['bao lâu nhận', 'thời gian giao', 'mấy ngày tới', 'giao hàng'], 'Thời gian giao dự kiến phụ thuộc địa chỉ và đơn vị vận chuyển, được hiển thị khi tính phí tại checkout. Với đơn đã đặt, xem mục Đơn hàng trong tài khoản hoặc yêu cầu nhân viên kiểm tra.', 200, false],
            ['payment', 'Phương thức thanh toán', 'payment', ['cod', 'thanh toán thế nào', 'phương thức thanh toán', 'chuyển khoản', 'thanh toán khi nhận'], 'Bạn có thể chọn COD hoặc phương thức đang được bật ở trang thanh toán. Các cổng online chỉ hoạt động khi cửa hàng đã cấu hình. Kiểm tra số tiền và hướng dẫn của đúng đơn trước khi thanh toán.', 240, false],
            ['payment_problem', 'Xác minh thanh toán', 'payment', ['đã thanh toán', 'bị trừ tiền', 'thanh toán thất bại', 'thanh toán lỗi', 'chưa cập nhật thanh toán'], 'Tình trạng thanh toán cần nhân viên xác minh. Hệ thống sẽ chuyển hội thoại sang chờ nhân viên; vui lòng không gửi mật khẩu, OTP hoặc số thẻ đầy đủ.', 600, true],
            ['order', 'Kiểm tra đơn hàng', 'order', ['kiểm tra đơn', 'đơn hàng của tôi', 'mã vận đơn', 'tra cứu đơn', 'đơn đang ở đâu'], 'Bạn đăng nhập và mở Đơn hàng trong tài khoản để xem trạng thái. Nếu cần hỗ trợ thêm, gửi mã đơn và chọn Yêu cầu nhân viên; thông tin đơn chỉ được cung cấp sau khi xác minh quyền sở hữu.', 350, false],
            ['order_change', 'Thay đổi hoặc hủy đơn', 'order', ['hủy đơn', 'huỷ đơn', 'đổi địa chỉ', 'sửa địa chỉ', 'thay đổi đơn'], 'Yêu cầu thay đổi hoặc hủy đơn cần kiểm tra trạng thái và quyền sở hữu. Hội thoại sẽ được chuyển cho nhân viên; tin nhắn này chưa thực hiện thay đổi trên đơn hàng.', 600, true],
            ['delivery_problem', 'Sự cố giao hàng', 'returns', ['giao chậm', 'chưa nhận được', 'giao thiếu', 'giao sai', 'hàng hỏng', 'bị vỡ', 'không nhận được hàng'], 'Nhân viên cần kiểm tra sự cố giao hàng. Bạn có thể gửi mã đơn và ảnh sản phẩm/kiện hàng trong hội thoại, tránh gửi thông tin nhạy cảm.', 650, true],
            ['returns', 'Đổi trả và hoàn tiền', 'returns', ['đổi trả', 'hoàn tiền', 'trả hàng', 'khiếu nại'], 'Nhân viên sẽ kiểm tra điều kiện đổi trả/hoàn tiền của đơn. Bạn có thể xem yêu cầu trong tài khoản; hệ thống chưa thực hiện hoàn tiền chỉ từ nội dung chat.', 650, true],
            ['care', 'Sử dụng và bảo quản', 'shopping', ['bảo quản', 'vệ sinh', 'cách sử dụng', 'hướng dẫn sử dụng'], 'Bạn xem hướng dẫn của đúng sản phẩm trên trang chi tiết. Nếu chưa rõ, gửi tên sản phẩm để nhân viên hỗ trợ; cách vệ sinh và sử dụng có thể khác nhau theo chất liệu.', 220, false],
            ['pet_health', 'Câu hỏi sức khỏe thú cưng', 'general', ['bị bệnh', 'uống thuốc', 'liều thuốc', 'điều trị', 'nôn ói'], 'Pawora không chẩn đoán hoặc hướng dẫn điều trị qua chat. Bạn nên liên hệ bác sĩ thú y để được đánh giá tình trạng của bé. Nhân viên chỉ hỗ trợ thông tin sản phẩm.', 700, false],
            ['contact', 'Liên hệ nhân viên', 'general', ['liên hệ shop', 'liên hệ nhân viên', 'cần hỗ trợ trực tiếp'], 'Hội thoại sẽ được chuyển cho nhân viên. Bạn có thể gửi thêm nội dung cần hỗ trợ tại đây.', 700, true],
        ];

        $legacyDefaults = $this->legacyDefaults();
        $legacyNames = ['discovery' => 'Tư vấn sản phẩm', 'contact' => 'Ngoài giờ hỗ trợ'];
        foreach ($templates as [$key, $name, $category, $keywords, $response, $priority, $handoff]) {
            $values = [
                'template_key' => $key, 'name' => $name, 'category' => $category, 'keywords' => $keywords,
                'response' => $response, 'priority' => $priority, 'handoff' => $handoff,
                'match_type' => 'contains', 'active' => true, 'delay_seconds' => 0,
                'once_per_conversation' => true, 'working_time_mode' => 'always', 'stop_after_match' => true,
                'start_time' => null, 'end_time' => null, 'weekdays' => null,
            ];
            $originalValues = $values;
            $values = $this->enhanceTemplate($values);
            $existing = ChatAutoReplyRule::where('template_key', $key)->first()
                ?? ChatAutoReplyRule::whereIn('name', [$name, $legacyNames[$key] ?? $name])->whereNull('template_key')->first();
            if (! $existing) {
                ChatAutoReplyRule::create($values);

                continue;
            }
            if ($existing->template_key) {
                // Upgrade untouched defaults only; keep staff edits and trigger history.
                if (collect($originalValues)->every(fn ($value, $field) => $existing->{$field} == $value)) {
                    $existing->update($values);
                }

                continue;
            }
            // Upgrade only an exact original demo template. Preserve staff edits and usage history.
            $original = $legacyDefaults[$existing->name] ?? null;
            $untouched = $original && collect($original)->every(fn ($value, $field) => $existing->{$field} == $value);
            $existing->update($untouched ? $values : ['template_key' => $key, 'category' => $category]);
        }
    }

    private function enhanceTemplate(array $values): array
    {
        $phrases = [
            'greeting' => ['chào', 'hi', 'alo', 'shop ơi'],
            'buying' => ['mua thế nào', 'mua sao', 'đặt sao', 'đặt kiểu gì', 'đặt như nào', 'mua như nào'],
            'discovery' => ['đồ cho chó', 'đồ cho mèo', 'đồ cho cún', 'đồ cho bé', 'tư vấn giúp', 'nên mua gì'],
            'size' => ['size nào', 'size gì', 'cỡ nào', 'cỡ gì', 'vừa không', 'vừa ko', 'màu nào', 'màu gì'],
            'stock' => ['còn không', 'còn hàng không', 'bao nhiêu tiền', 'giá nhiêu', 'giá sao', 'bao nhiêu một', 'còn bán'],
            'voucher' => ['mã giảm', 'mã khuyến mại', 'mã khuyến mãi', 'ưu đãi', 'khuyến mại', 'code giảm', 'xin mã', 'có mã nào', 'sale', 'discount', 'giamgia'],
            'voucher_error' => ['mã bị lỗi', 'mã lỗi', 'mã không dùng được', 'voucher không dùng được', 'không xài được mã', 'mã không áp dụng', 'voucher không áp dụng', 'mã không được', 'voucher không được'],
            'shipping' => ['tiền ship', 'ship bao nhiêu', 'ship nhiêu', 'ship bn', 'ship về', 'free ship', 'miễn ship', 'có ship', 'có giao đến', 'có giao tới'],
            'delivery_eta' => ['bao giờ nhận', 'khi nào nhận', 'khi nào tới', 'khi nào đến', 'bao lâu tới', 'bao lâu giao', 'ship mấy ngày', 'ship bao lâu'],
            'payment' => ['trả tiền sao', 'trả tiền thế nào', 'thanh toán sao', 'thanh toán bằng gì', 'momo', 'vnpay', 'zalopay', 'sepay', 'nhận hàng rồi trả'],
            'payment_problem' => ['trừ tiền rồi', 'trừ tiền mà', 'trừ tiền nhưng', 'trả tiền rồi', 'chuyển tiền rồi', 'chuyển khoản rồi', 'thanh toán rồi', 'không thanh toán được'],
            'order' => ['đơn tới đâu', 'đơn đến đâu', 'đơn ở đâu', 'đơn của mình', 'đơn của tôi', 'xem đơn', 'tra đơn', 'theo dõi đơn', 'tracking'],
            'order_change' => ['hủy giúp', 'huỷ giúp', 'hủy giúp đơn', 'đổi nơi nhận', 'đổi số điện thoại nhận', 'sửa đơn'],
            'delivery_problem' => ['chưa thấy hàng', 'chưa có hàng tới', 'thiếu món', 'thiếu sản phẩm', 'sai sản phẩm', 'bị bể', 'hàng lỗi'],
            'returns' => ['trả lại hàng', 'đổi lại hàng', 'đòi tiền lại', 'lấy lại tiền', 'refund'],
            'care' => ['giặt được không', 'rửa được không', 'dùng thế nào', 'dùng làm sao'],
        ];
        $values['keywords'] = array_values(array_unique([...$values['keywords'], ...($phrases[$values['template_key']] ?? [])]));
        $values['once_per_conversation'] = $values['template_key'] === 'greeting';
        if ($values['template_key'] === 'voucher') {
            $values['response'] = 'Bạn xem mã giảm giá trong Ví voucher nhé: bấm Chủ đề hỗ trợ → Mở Ví voucher, hoặc đăng nhập rồi vào Tài khoản → Ví voucher. Khi thanh toán, bấm Chọn hoặc nhập mã để chọn mã phù hợp. Bạn muốn tìm mã hay cần hướng dẫn cách dùng?';
        }

        return $values;
    }

    private function legacyDefaults(): array
    {
        $rules = [
            ['Lời chào', ['xin chào', 'hello', 'chào shop'], 'contains', 'Xin chào! Pawora đã nhận được tin nhắn. Bạn cần hỗ trợ sản phẩm hay đơn hàng ạ?', 100, 'always'],
            ['Phí giao hàng', ['phí ship', 'phí vận chuyển', 'giao hàng'], 'contains', 'Pawora tính phí theo khu vực và sẽ hiển thị chính xác ở bước thanh toán. Đơn đủ điều kiện sẽ được miễn phí vận chuyển.', 90, 'always'],
            ['Đổi trả và hoàn tiền', ['đổi trả', 'hoàn tiền', 'trả hàng'], 'contains', 'Pawora hỗ trợ yêu cầu đổi trả trong vòng 7 ngày kể từ khi nhận hàng. Bạn gửi giúp mã đơn và tình trạng sản phẩm nhé.', 85, 'always'],
            ['Kiểm tra đơn hàng', ['mã vận đơn', 'đơn hàng của tôi', 'kiểm tra đơn'], 'contains', 'Bạn gửi mã đơn hàng để Pawora kiểm tra hành trình và mã vận đơn chính xác giúp bạn nhé.', 95, 'always'],
            ['Tư vấn sản phẩm', ['tư vấn sản phẩm', 'chọn thức ăn', 'chọn size'], 'contains', 'Bạn cho Pawora biết loại thú cưng, cân nặng và nhu cầu sử dụng. Nhân viên sẽ tư vấn lựa chọn phù hợp nhất.', 80, 'always'],
            ['Ngoài giờ hỗ trợ', ['hỗ trợ', 'nhân viên', 'tư vấn'], 'contains', 'Pawora đã ghi nhận yêu cầu ngoài giờ. Đội ngũ sẽ phản hồi từ 08:00 đến 18:00 trong ngày làm việc gần nhất.', 30, 'outside_hours'],
        ];
        $defaults = [];
        foreach ($rules as $index => [$name, $keywords, $matchType, $response, $priority, $timeMode]) {
            $defaults[$name] = [
                'name' => $name, 'keywords' => $keywords, 'match_type' => $matchType,
                'response' => $response, 'priority' => $priority, 'delay_seconds' => $index % 3,
                'once_per_conversation' => true, 'active' => true, 'working_time_mode' => $timeMode,
                'start_time' => $timeMode === 'always' ? null : '08:00:00',
                'end_time' => $timeMode === 'always' ? null : '18:00:00',
                'weekdays' => $timeMode === 'always' ? null : [1, 2, 3, 4, 5, 6],
                'stop_after_match' => true,
            ];
        }

        return $defaults;
    }
}
