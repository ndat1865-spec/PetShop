# Pawora Commerce

Pawora Commerce quản lý vòng đời đơn hàng của cửa hàng thú cưng từ lúc khách đặt hàng đến khi giao nhận, thanh toán và đổi trả hoàn tất.

## Language

**Xác nhận nhận hàng của khách**:
Mốc cho biết khách hàng xác nhận đã nhận được kiện hàng. Mốc này không tự hoàn tất đơn hàng và phải có trước khi admin xác nhận giao thành công.
_Avoid_: Đã giao hàng, hoàn tất đơn

**Giao hàng hoàn tất**:
Trạng thái cuối của giao nhận, được admin xác nhận sau khi khách đã xác nhận nhận hàng. Mốc này bắt đầu thời hạn đổi trả và hoàn tất thanh toán COD.
_Avoid_: Khách xác nhận nhận hàng
