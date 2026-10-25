# QR Welcome: xác nhận lượt quét, chống gửi trùng

Ngày kiểm tra: **25/10/2026**. Nhánh gốc: `claude/qr-code-welcome-system-44fHI`, commit `1167444e58109d73cfe0da7340d38fa1dc573139`.

## Vấn đề đã xác nhận

- `routes/web.php` gọi `WelcomeController::show()` qua GET `/welcome/{qrCode}`. Phương thức này vừa hiển thị vừa tạo `checkins`, cập nhật `checkout_at`, gọi `Guest::recordScan()` và tự khóa QR khi hết lượt.
- Refresh, mở lại URL hoặc preview/prefetch có thể ghi nhận thêm lượt, thậm chí đổi ngay từ vào sang ra.
- `canScan()`, chuyển trạng thái và `recordScan()` không nằm trong một giao dịch/khóa chung: hai request có thể cùng vượt qua kiểm tra giới hạn.
- JavaScript `isRedirecting` chỉ giảm callback camera lặp trong một trang; không bảo vệ nhiều tab, nhiều thiết bị hoặc HTTP retry.
- `tests/Feature/ExampleTest.php` chỉ kiểm tra trang chủ.

Nguồn gốc: [WelcomeController](https://github.com/tranhuytvc/tvc/blob/1167444e58109d73cfe0da7340d38fa1dc573139/app/Http/Controllers/WelcomeController.php), [Guest](https://github.com/tranhuytvc/tvc/blob/1167444e58109d73cfe0da7340d38fa1dc573139/app/Models/Guest.php), [routes](https://github.com/tranhuytvc/tvc/blob/1167444e58109d73cfe0da7340d38fa1dc573139/routes/web.php).

## Luồng được chọn

1. QR cũ và scanner vẫn mở GET `/welcome/{qrCode}?station=...`.
2. Trang giữ logo, media khách, nền và nội dung sự kiện; hiển thị rõ **chưa ghi nhận lượt quét**, tên thao tác và nút **Xác nhận vào / Xác nhận ra**. Không tự submit và không tự chuyển trang khi đang chờ xác nhận.
3. Người dùng bấm nút → POST `/welcome/{qrCode}/scan`, kèm `@csrf` và token xác nhận do máy chủ mã hóa/xác thực bằng Laravel Crypt.
4. Máy chủ xác nhận và ghi nhận trong một giao dịch.
5. Trả **303 See Other** đến GET `/welcome/{qrCode}?receipt={request_id}`. Trang kết quả dùng giao diện thành công và bộ đếm quay về scanner hiện có.

**Đánh đổi trải nghiệm:** mỗi lượt cần thêm một lần bấm xác nhận. Camera nhận diện QR chỉ mở trang, chưa thay đổi khách. Đây là chủ đích để việc mở URL luôn an toàn. Nếu sau này cần trạm quét tự động, cần một endpoint POST dành cho trạm đã xác thực; không khôi phục ghi dữ liệu bằng GET hoặc tự POST khi trang vừa tải.

## Chống trùng và tranh chấp

`QrScanService` chịu trách nhiệm nghiệp vụ, controller chỉ điều phối HTTP.

- Token chứa UUID thao tác, QR, trạm, thao tác vào/ra, dấu vân tay trạng thái và hạn 10 phút. Không tin `action` hoặc `station` tùy ý trong POST để quyết định ghi dữ liệu.
- `scan_operations.request_id` có unique index. Cùng token gửi lại trả cùng receipt, kể cả lượt trước vừa làm QR tự khóa hoặc token đã hết hạn sau khi thực hiện thành công. Không tăng đếm lần nữa.
- `scan_version` tăng sau mỗi lượt thành công và không bị CMS reset bộ đếm. Dấu vân tay còn bao gồm bộ đếm, chế độ, giới hạn, trạng thái khóa/kích hoạt và bản ghi vào/ra hiện tại. Token của tab cũ không thể đổi thành thao tác khác khi submit.
- `DB::transaction(..., attempts: 5)` và `lockForUpdate()` khóa hàng khách trước khi đọc trạng thái. Lưu check-in/check-out, tăng đếm, tự khóa và receipt trong cùng giao dịch. Có thêm cập nhật có điều kiện theo `scan_version` để bảo vệ việc chuyển trạng thái trên SQLite, nơi `FOR UPDATE` không có khóa hàng như MySQL.
- UUID không thay thế xử lý đồng thời: hai token khác nhau nhưng cùng trạng thái vẫn bị kiểm tra lại. Chỉ một token được thực hiện.
- Khoảng chặn **5 giây**, tính từ lượt thành công cuối của khách, áp dụng chung mọi thiết bị/trạm. Một token mới trong khoảng này nhận 429; HTTP retry cùng token cũ vẫn trả kết quả cũ. Cấu hình tại `config/welcome.php`.
- Token hết hạn nhận 419; trạng thái cũ nhận 409; bị khóa/hết lượt nhận 403; token sai nhận 422; QR/trạm không tồn tại hoặc trạm bị vô hiệu hóa nhận 404. Các lỗi nghiệp vụ có thông báo tiếng Việt và đường dẫn mở lại xác nhận; 429 có `Retry-After`.
- Trạm được chỉ định nhưng không hợp lệ không được âm thầm chuyển sang chế độ toàn hệ thống.
- GET xác nhận, GET kết quả, HEAD, preview và refresh không ghi check-in/check-out, không đổi bộ đếm, khóa hoặc phiên bản. Các trang cá nhân dùng `Cache-Control: no-store, private`. Middleware session của Laravel vẫn hoạt động bình thường.

Các form nằm trong nhóm middleware `web` của Laravel 13, sử dụng cơ chế chống CSRF hiện có. CSRF không thay thế xác thực nhân viên: bản sửa giữ nguyên cơ chế QR công khai đang có.

## Quy tắc từng chế độ

| Chế độ | Lượt thành công | Khi đạt giới hạn |
|---|---|---|
| `one_time` | Một check-in, không checkout | Tăng đếm lên 1, khóa QR |
| `checkin_checkout` | Tối đa 2 lượt tổng cộng; từ trạng thái ban đầu là vào rồi ra | Sau lượt 2 khóa QR |
| `max_scans` | Luân phiên vào/ra, mỗi thao tác hợp lệ tiêu thụ 1 lượt | Sau N lượt khóa QR; N lẻ có thể kết thúc ở trạng thái đang ở trong |
| `unlimited` | Luân phiên vào/ra không giới hạn | Không tự khóa; vẫn chống trùng và áp dụng khoảng chặn |

Giữ nguyên quy tắc khóa, giới hạn và thao tác reset quota của CMS. Reset không xóa lịch sử check-in. Riêng `one_time` luôn tạo check-in sau reset, đúng nhãn “chỉ check-in”, không checkout bản ghi cũ. `checkin_checkout` và `max_scans` tiếp tục dựa trên trạng thái hiện tại sau reset như trước.

Không thay đổi controller quản lý khách, CRUD/import/export, QR đã phát, phân quyền, cấu hình trạm hoặc API/màn hình display. Receipt lưu riêng thao tác để trang kết quả check-in cũ không biến thành checkout khi khách thực sự rời sự kiện sau đó. Chức năng polling của display giữ nguyên hợp đồng dữ liệu hiện tại.

## Kiểm thử

`tests/Feature/WelcomeScanTest.php` dùng `RefreshDatabase`:

- GET/HEAD/prefetch lặp: toàn bộ hàng khách không thay đổi, không có check-in hoặc receipt.
- Từng chế độ, gồm N = 1, N lẻ và N chẵn: đúng thứ tự, số bản ghi, khóa và quota.
- HTTP retry cùng token, refresh kết quả, receipt cũ sau checkout, retry sau lượt cuối làm tự khóa.
- Hai tab khác token; xác nhận mới trong khoảng chặn và checkout hợp lệ sau đó.
- QR không hoạt động/khóa/hết lượt, thay đổi chính sách sau khi mở xác nhận.
- Token giả, thiếu, hết hạn, dùng cho khách khác; receipt không thuộc khách.
- Trạm hợp lệ, không tồn tại, bị vô hiệu hóa; giả trường action/station trong POST không đổi mục tiêu ghi.
- Cấu hình sự kiện, trang display và API display theo trạm vẫn trả kết quả check-in.
- Reset quota giữ lịch sử và chống replay; lỗi giữa giao dịch hoàn tác cả check-in, quota, khóa, phiên bản và receipt.
- CSRF: bật hành vi middleware thật trong bài kiểm thử, xác nhận thiếu/sai token bị chặn và token đúng được nhận.

`tests/Feature/WelcomeScanConcurrencyTest.php` chạy hai tiến trình PHP với hai kết nối DB, đồng bộ điểm bắt đầu; kiểm tra cùng token và khác token cho cả 4 chế độ. Dùng file SQLite tạm riêng, không xóa database của lập trình viên. Đây là kiểm thử đồng thời thực, không phải gọi hai request nối tiếp.

Chạy:

```sh
composer install
php artisan test
```

`phpunit.xml` bổ sung APP_KEY dành riêng cho test để test không cần `.env` thật. Không sử dụng khóa đó khi triển khai.

Kết quả trong môi trường chuẩn bị bản sửa: **36 tests / 467 assertions thành công**, PHP 8.3.6, Laravel 13.12.0 theo `composer.lock`, SQLite. Đã kiểm tra định dạng PHP và diff. Chưa chạy tích hợp MySQL/InnoDB hoặc thao tác camera trên thiết bị thật; cần kiểm tra hai yêu cầu đồng thời, deadlock retry và lượt cuối trên cấu hình MySQL triển khai trước khi phát hành.

## Triển khai

1. Kiểm tra bản sửa trên staging và sao lưu DB theo quy trình hiện có.
2. Tạm ngừng tiếp nhận scan khi chuyển phiên bản: không để node cũ còn ghi bằng GET trong lúc node mới chạy POST.
3. Chạy `php artisan migrate --force`; migration thêm `guests.scan_version` và bảng `scan_operations`, không sửa/xóa check-in hay bộ đếm cũ.
4. Deploy toàn bộ code, `php artisan optimize:clear`; khởi động lại worker/tiến trình chạy lâu nếu có. Giữ APP_KEY hiện tại và cấu hình session đúng giữa các node.
5. Kiểm tra URL QR đã phát, trạm, CSRF, xác nhận vào/ra và display trước khi mở lại lượt quét.

Không xóa receipt hoặc reset `scan_version` khi reset quota. Nếu cần chính sách dọn lịch sử trong tương lai, phải đồng thời thiết kế lại thời hạn đảm bảo replay. Nếu rollback về code cũ, rủi ro ghi bằng GET trở lại; chỉ rollback trong thời gian tạm ngừng quét.

Tham khảo Laravel: [CSRF](https://laravel.com/framework/docs/13.x/csrf), [transaction](https://laravel.com/framework/docs/13.x/database), [khóa dữ liệu](https://laravel.com/framework/docs/13.x/queries#pessimistic-locking).
