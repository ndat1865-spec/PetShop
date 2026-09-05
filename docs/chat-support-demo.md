# Demo CSKH: bot → nhân viên

Chat dùng quy tắc trong database, không dùng dịch vụ AI bên ngoài. Khách có thể yêu cầu nhân viên ngay cả khi bot trả lời được.

## Cập nhật một bản cài đang có dữ liệu

```powershell
php artisan migrate --path=database/migrations/2026_09_05_000002_add_chat_handoff_and_delivery_tracking.php
php artisan db:seed --class=ChatSupportRuleSeeder
npm run build
```

Không cần reset database hoặc chạy lại toàn bộ `DatabaseSeeder`. Seeder CSKH bổ sung 18 mẫu theo chủ đề; chỉ thay mẫu cũ khi mọi trường cấu hình vẫn khớp bản demo gốc. Nội dung/bật tắt/ưu tiên đã chỉnh và lịch sử kích hoạt được giữ nguyên. Chạy lại không tạo bản trùng, kể cả sau khi đổi tên mẫu. Muốn bỏ một mẫu lâu dài, hãy tắt nó; xóa rồi seed lại sẽ tạo lại mẫu đó.

## Thử luồng chính

1. Mở cửa hàng và widget chat trong một trình duyệt dành cho khách. Gửi “Tôi cần được xem mã giảm giá”: bot hướng dẫn mở Ví voucher.
2. Gửi một câu ngoài bộ quy tắc: bot đưa lời nhắc ngắn và mở các chủ đề hỗ trợ. Nút **Yêu cầu nhân viên** luôn có trong hội thoại.
3. Bấm nút đó hoặc gửi “Tôi muốn gặp người thật”. Hội thoại chuyển sang **Đang chờ nhân viên**; tải lại vẫn giữ trạng thái này. Khách có thể gửi thêm thông tin.
4. Ở `/admin/tin-nhan`, lọc **Chờ nhân viên**, mở hội thoại và bấm **Tiếp nhận**. Người phụ trách được lưu; nhân viên khác không thể dùng Tiếp nhận để giành hội thoại đã phân công.
5. Trả lời ở ô dưới khung chat. Bot đã tạm dừng, cả phản hồi có độ trễ đang chờ cũng bị hủy. Chỉ bật lại bằng tùy chọn **Bật lại bot** và lưu trạng thái.
6. Thử “Tôi đã thanh toán nhưng đơn chưa cập nhật” hoặc “Tôi muốn hoàn tiền” trong hội thoại mới: quy tắc chuyển nhân viên để xác minh, không thay đổi đơn hay thanh toán.

Dùng dữ liệu giả khi demo. Xem đơn, voucher và các thao tác tài khoản vẫn theo quyền sở hữu hiện có; chat không cung cấp dữ liệu đơn chỉ dựa vào mã đơn.

## Soạn tin và quản lý quy tắc

- Desktop có ba cột; ô soạn luôn ở trong viewport. Tablet/mobile có nút mở thông tin khách và quay lại danh sách.
- Enter gửi; Shift+Enter xuống dòng. Enter đang ghép chữ bằng IME không gửi. Bản nháp văn bản của nhân viên được giữ theo hội thoại trong tab trình duyệt; file đính kèm cần chọn lại sau reload.
- Khi đọc lịch sử, tin mới không tự kéo xuống; dùng nút **Tin nhắn mới** để quay về cuối.
- Ở `/admin/tin-nhan/tra-loi-tu-dong`, lọc chủ đề/tìm từ khóa, bật tắt hoặc chỉnh quy tắc. Số ưu tiên lớn hơn được xét trước; yêu cầu người thật và ý định cụ thể được ưu tiên hơn lời chào.
- **Thử câu hỏi** chọn một kết quả dự kiến mà không ghi tin, tăng thống kê hay sửa hội thoại. Chế độ thử giả định hội thoại mới đang bật bot; hội thoại thật còn kiểm tra giới hạn một lần, cooldown 60 giây và trạng thái nhân viên.

## Kiểm thử và giới hạn

Sau khi chuẩn bị database test riêng theo README:

```powershell
php artisan test --filter="CustomerChatTest|ChatHandoffTest"
npm run build
```

Khi Reverb không kết nối, widget cập nhật bằng polling khoảng 3,5 giây; hộp thư khoảng 4 giây. Giao diện chỉ báo realtime khi kết nối thực sự hoạt động. Kết nối HTTP tới Reverb được giới hạn 0,2 giây khi kết nối và 0,5 giây tổng thời gian; sau một lần lỗi, chat tạm dừng thử gửi realtime 15 giây rồi tự thử lại. Tin nhắn vẫn được lưu và polling tiếp tục hoạt động. Phản hồi có độ trễ được phát ở lượt đọc/poll tiếp theo sau hạn gửi, nên không cam kết đúng từng giây khi không có ai mở chat.

Bot chỉ đưa hướng dẫn từ quy tắc. Giá, tồn kho, voucher và tình trạng giao dịch vẫn cần xem ở trang chức năng hoặc nhân viên xác minh. Không có cam kết thời gian trả lời/nhân viên online khi hệ thống chưa có dữ liệu hiện diện. Chưa phân trang lịch sử tin nhắn; hội thoại rất dài sẽ tăng dung lượng tải.
