# Playwright E2E — Đăng nhập và đăng ký

## Điều kiện chạy

- Apache và MySQL trong XAMPP đang hoạt động.
- Website truy cập được tại `http://localhost/PetShopnew/public/`.
- Google Chrome đã được cài trên máy.

## Các lệnh chính

```powershell
# Chạy toàn bộ test và tạo HTML report
npm run test:e2e

# Mở giao diện Playwright để chạy từng test
npm run test:e2e:ui

# Nhìn thấy Chrome chạy tự động
npm run test:e2e:headed

# Mở lại báo cáo của lần chạy gần nhất
npm run test:e2e:report
```

HTML report được tạo tại `playwright-report/index.html`. Mỗi test đính kèm ảnh chụp trạng thái cuối để dùng làm minh chứng trong bài thuyết trình. Khi test thất bại, Playwright còn giữ screenshot, video và trace phục vụ phân tích lỗi.

Ca đăng ký thành công dùng email có timestamp để không bị trùng giữa các lần chạy và sẽ tạo một tài khoản mới trong database local.
