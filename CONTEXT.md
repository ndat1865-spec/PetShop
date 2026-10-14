# Pawora Commerce

Pawora Commerce quản lý vòng đời đơn hàng của cửa hàng thú cưng từ lúc khách đặt hàng đến khi giao nhận, thanh toán và đổi trả hoàn tất.

## Language

**Xác nhận nhận hàng của khách**:
Mốc tùy chọn khi khách bấm “Tôi đã nhận được hàng” cho đơn đang giao hoặc đã giao thành công. Mốc này hiển thị là “Đã nhận hàng”; nếu giao vận chưa hoàn tất thì xác nhận cũng hoàn tất giao hàng, còn nếu giao vận đã hoàn tất thì ngày giao và thời hạn đổi trả giữ nguyên.
_Avoid_: Đã giao hàng, hoàn tất đơn

**Giao hàng hoàn tất**:
Trạng thái cuối của giao nhận, đạt được khi khách xác nhận nhận hàng, hoặc khi admin xác nhận theo báo cáo giao thành công của đơn vị vận chuyển nếu khách chưa bấm. Mốc này bắt đầu thời hạn đổi trả, hoàn tất thanh toán COD và mở quyền đánh giá sản phẩm.
_Avoid_: Khách xác nhận nhận hàng

**Trao đổi đánh giá**:
Chuỗi tin công khai dưới một đánh giá, chỉ gồm người viết đánh giá và shop. Khách dùng nó để cập nhật sau một thời gian dùng sản phẩm hoặc trả lời shop; số sao và nội dung gốc vẫn chỉ đổi bằng cách sửa đánh giá. Trao đổi được giữ nguyên khi khách sửa đánh giá.
_Avoid_: Bình luận, phản hồi shop (khi chỉ nói về một tin)

**Chờ shop trả lời**:
Đánh giá đang hiển thị mà chưa có tin nào của shop, hoặc tin cuối trong trao đổi là của khách, hoặc khách sửa đánh giá sau tin cuối. Shop trả lời trong trao đổi sẽ kết thúc trạng thái này.
_Avoid_: Đánh giá chưa phản hồi

**Ẩn đánh giá**:
Admin gỡ đánh giá khỏi trang sản phẩm và điểm trung bình. Bắt buộc ghi lý do trong ghi chú nội bộ; ghi chú này khách không thấy.
