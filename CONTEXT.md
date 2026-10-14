# Pawora Commerce

Pawora Commerce quản lý vòng đời đơn hàng của cửa hàng thú cưng từ lúc khách đặt hàng đến khi giao nhận, thanh toán và đổi trả hoàn tất.

## Language

**Xác nhận nhận hàng của khách**:
Mốc tùy chọn khi khách bấm “Tôi đã nhận được hàng” cho đơn đang giao hoặc đã giao thành công. Mốc này hiển thị là “Đã nhận hàng”; nếu giao vận chưa hoàn tất thì xác nhận cũng hoàn tất giao hàng, còn nếu giao vận đã hoàn tất thì ngày giao và thời hạn đổi trả giữ nguyên.
_Avoid_: Đã giao hàng, hoàn tất đơn

**Giao hàng hoàn tất**:
Trạng thái cuối của giao nhận, đạt được khi khách xác nhận nhận hàng, hoặc khi admin xác nhận theo báo cáo giao thành công của đơn vị vận chuyển nếu khách chưa bấm. Mốc này bắt đầu thời hạn đổi trả, hoàn tất thanh toán COD và mở quyền đánh giá sản phẩm.
_Avoid_: Khách xác nhận nhận hàng
