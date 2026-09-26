
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `admin_audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `actor_user_id` bigint(20) unsigned DEFAULT NULL,
  `event` varchar(255) NOT NULL,
  `auditable_type` varchar(255) DEFAULT NULL,
  `auditable_id` bigint(20) unsigned DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `admin_audit_logs_actor_user_id_foreign` (`actor_user_id`),
  KEY `admin_audit_logs_auditable_type_auditable_id_index` (`auditable_type`,`auditable_id`),
  KEY `admin_audit_logs_event_index` (`event`),
  KEY `admin_audit_logs_created_at_index` (`created_at`),
  CONSTRAINT `admin_audit_logs_actor_user_id_foreign` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `admin_audit_logs` WRITE;
/*!40000 ALTER TABLE `admin_audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_audit_logs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`),
  KEY `categories_parent_id_foreign` (`parent_id`),
  KEY `categories_is_active_index` (`is_active`),
  CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,NULL,'Đồ chơi cho chó','do-choi-cho-cho','Phụ kiện được Pawora tuyển chọn cho những khoảnh khắc gần gũi mỗi ngày.',NULL,1,'2026-09-26 08:47:32','2026-09-26 08:47:32',NULL),(2,NULL,'Đồ chơi cho mèo','do-choi-cho-meo','Phụ kiện được Pawora tuyển chọn cho những khoảnh khắc gần gũi mỗi ngày.',NULL,1,'2026-09-26 08:47:32','2026-09-26 08:47:32',NULL),(3,NULL,'Vòng cổ & dây dắt','vong-co-day-dat','Phụ kiện được Pawora tuyển chọn cho những khoảnh khắc gần gũi mỗi ngày.',NULL,1,'2026-09-26 08:47:32','2026-09-26 08:47:32',NULL),(4,NULL,'Giường & chăn','giuong-chan','Phụ kiện được Pawora tuyển chọn cho những khoảnh khắc gần gũi mỗi ngày.',NULL,1,'2026-09-26 08:47:32','2026-09-26 08:47:32',NULL),(5,NULL,'Bát ăn & bình nước','bat-an-binh-nuoc','Phụ kiện được Pawora tuyển chọn cho những khoảnh khắc gần gũi mỗi ngày.',NULL,1,'2026-09-26 08:47:32','2026-09-26 08:47:32',NULL),(6,NULL,'Grooming','grooming','Phụ kiện được Pawora tuyển chọn cho những khoảnh khắc gần gũi mỗi ngày.',NULL,1,'2026-09-26 08:47:32','2026-09-26 08:47:32',NULL),(7,NULL,'Quần áo thú cưng','quan-ao-thu-cung','Phụ kiện được Pawora tuyển chọn cho những khoảnh khắc gần gũi mỗi ngày.',NULL,1,'2026-09-26 08:47:32','2026-09-26 08:47:32',NULL),(8,NULL,'Phụ kiện du lịch','phu-kien-du-lich','Phụ kiện được Pawora tuyển chọn cho những khoảnh khắc gần gũi mỗi ngày.',NULL,1,'2026-09-26 08:47:32','2026-09-26 08:47:32',NULL);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `chat_auto_reply_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_auto_reply_rules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`keywords`)),
  `match_type` varchar(20) NOT NULL DEFAULT 'contains',
  `response` text NOT NULL,
  `priority` smallint(5) unsigned NOT NULL DEFAULT 50,
  `delay_seconds` smallint(5) unsigned NOT NULL DEFAULT 1,
  `once_per_conversation` tinyint(1) NOT NULL DEFAULT 1,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `working_time_mode` varchar(20) NOT NULL DEFAULT 'always',
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `weekdays` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`weekdays`)),
  `stop_after_match` tinyint(1) NOT NULL DEFAULT 1,
  `trigger_count` int(10) unsigned NOT NULL DEFAULT 0,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `category` varchar(40) NOT NULL DEFAULT 'general',
  `template_key` varchar(255) DEFAULT NULL,
  `handoff` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chat_auto_reply_rules_template_key_unique` (`template_key`),
  KEY `chat_auto_reply_rules_created_by_foreign` (`created_by`),
  KEY `chat_auto_reply_rules_updated_by_foreign` (`updated_by`),
  KEY `chat_auto_reply_rules_active_priority_index` (`active`,`priority`),
  CONSTRAINT `chat_auto_reply_rules_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chat_auto_reply_rules_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `chat_auto_reply_rules` WRITE;
/*!40000 ALTER TABLE `chat_auto_reply_rules` DISABLE KEYS */;
INSERT INTO `chat_auto_reply_rules` VALUES (1,'Lời chào','[\"xin ch\\u00e0o\",\"hello\",\"ch\\u00e0o shop\"]','contains','Chào bạn! Bạn cần tìm sản phẩm, xem voucher hay hỗ trợ đơn hàng? Bạn cũng có thể chọn Yêu cầu nhân viên.',10,0,1,1,'always',NULL,NULL,NULL,1,0,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33','general','greeting',0),(2,'Hướng dẫn đặt hàng','[\"c\\u00e1ch mua\",\"\\u0111\\u1eb7t h\\u00e0ng th\\u1ebf n\\u00e0o\",\"h\\u01b0\\u1edbng d\\u1eabn mua\",\"mua h\\u00e0ng\"]','contains','Bạn chọn sản phẩm và biến thể, thêm vào giỏ rồi đăng nhập để thanh toán. Giá, phí ship và voucher được kiểm tra ở bước thanh toán.',180,0,1,1,'always',NULL,NULL,NULL,1,0,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33','shopping','buying',0),(3,'Tìm sản phẩm phù hợp','[\"t\\u01b0 v\\u1ea5n s\\u1ea3n ph\\u1ea9m\",\"s\\u1ea3n ph\\u1ea9m cho ch\\u00f3\",\"s\\u1ea3n ph\\u1ea9m cho m\\u00e8o\",\"ng\\u00e2n s\\u00e1ch\",\"t\\u00ecm s\\u1ea3n ph\\u1ea9m\"]','contains','Bạn cho Pawora biết bé là chó hay mèo, nhu cầu sử dụng và ngân sách dự kiến nhé. Xem danh mục sản phẩm hoặc chọn Yêu cầu nhân viên để được tư vấn cụ thể.',200,0,1,1,'always',NULL,NULL,NULL,1,0,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33','shopping','discovery',0),(4,'Chọn size và màu','[\"ch\\u1ecdn size\",\"k\\u00edch th\\u01b0\\u1edbc\",\"ch\\u1ecdn m\\u00e0u\",\"\\u0111\\u1ed5i m\\u00e0u\",\"bi\\u1ebfn th\\u1ec3\"]','contains','Bạn mở trang sản phẩm để xem các size/màu hiện có. Gửi tên sản phẩm và số đo phù hợp (ví dụ vòng ngực với yếm), nhân viên sẽ hỗ trợ chọn size; không chỉ dựa vào cân nặng.',220,0,1,1,'always',NULL,NULL,NULL,1,0,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33','shopping','size',0),(5,'Giá và tồn kho','[\"c\\u00f2n h\\u00e0ng\",\"h\\u1ebft h\\u00e0ng\",\"gi\\u00e1 bao nhi\\u00eau\",\"t\\u1ed3n kho\",\"khi n\\u00e0o c\\u00f3 h\\u00e0ng\"]','contains','Giá và tồn kho phụ thuộc sản phẩm và biến thể đang chọn. Bạn gửi tên hoặc đường dẫn sản phẩm để nhân viên kiểm tra; Pawora chưa thể xác nhận ngày nhập lại qua câu trả lời tự động.',230,0,1,1,'always',NULL,NULL,NULL,1,0,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33','shopping','stock',0),(6,'Xem mã giảm giá','[\"m\\u00e3 gi\\u1ea3m gi\\u00e1\",\"voucher\",\"khuy\\u1ebfn m\\u00e3i\",\"m\\u00e3 \\u01b0u \\u0111\\u00e3i\",\"coupon\",\"gi\\u1ea3m gi\\u00e1\"]','contains','Bạn đăng nhập rồi mở Ví voucher trong tài khoản để xem voucher hiện có. Khi thanh toán, hệ thống kiểm tra hạn dùng, giá trị đơn tối thiểu và lượt sử dụng. Pawora không tự tạo mã ngoài danh sách đang có.',300,0,1,1,'always',NULL,NULL,NULL,1,0,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33','voucher','voucher',0),(7,'Voucher không áp dụng được','[\"kh\\u00f4ng d\\u00f9ng \\u0111\\u01b0\\u1ee3c m\\u00e3\",\"kh\\u00f4ng \\u00e1p d\\u1ee5ng \\u0111\\u01b0\\u1ee3c\",\"m\\u00e3 kh\\u00f4ng h\\u1ee3p l\\u1ec7\",\"voucher l\\u1ed7i\",\"m\\u00e3 h\\u1ebft h\\u1ea1n\"]','contains','Bạn kiểm tra hạn dùng, giá trị đơn tối thiểu và giới hạn sử dụng trong Ví voucher. Nếu vẫn lỗi, gửi tên mã và thông báo tại checkout, hoặc chọn Yêu cầu nhân viên. Không cần gửi mật khẩu hay thông tin thẻ.',400,0,1,1,'always',NULL,NULL,NULL,1,0,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33','voucher','voucher_error',0),(8,'Phí giao hàng','[\"ph\\u00ed ship\",\"ph\\u00ed v\\u1eadn chuy\\u1ec3n\",\"mi\\u1ec5n ph\\u00ed ship\",\"freeship\",\"khu v\\u1ef1c giao\"]','contains','Pawora tính phí theo địa chỉ và giá trị đơn. Bạn chọn địa chỉ ở bước thanh toán để xem phí chính xác và điều kiện miễn phí vận chuyển đang áp dụng.',250,0,1,1,'always',NULL,NULL,NULL,1,0,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33','shipping','shipping',0),(9,'Thời gian giao dự kiến','[\"bao l\\u00e2u nh\\u1eadn\",\"th\\u1eddi gian giao\",\"m\\u1ea5y ng\\u00e0y t\\u1edbi\",\"giao h\\u00e0ng\"]','contains','Thời gian giao dự kiến phụ thuộc địa chỉ và đơn vị vận chuyển, được hiển thị khi tính phí tại checkout. Với đơn đã đặt, xem mục Đơn hàng trong tài khoản hoặc yêu cầu nhân viên kiểm tra.',200,0,1,1,'always',NULL,NULL,NULL,1,0,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33','shipping','delivery_eta',0),(10,'Phương thức thanh toán','[\"cod\",\"thanh to\\u00e1n th\\u1ebf n\\u00e0o\",\"ph\\u01b0\\u01a1ng th\\u1ee9c thanh to\\u00e1n\",\"chuy\\u1ec3n kho\\u1ea3n\",\"thanh to\\u00e1n khi nh\\u1eadn\"]','contains','Bạn có thể chọn COD hoặc phương thức đang được bật ở trang thanh toán. Các cổng online chỉ hoạt động khi cửa hàng đã cấu hình. Kiểm tra số tiền và hướng dẫn của đúng đơn trước khi thanh toán.',240,0,1,1,'always',NULL,NULL,NULL,1,0,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33','payment','payment',0),(11,'Xác minh thanh toán','[\"\\u0111\\u00e3 thanh to\\u00e1n\",\"b\\u1ecb tr\\u1eeb ti\\u1ec1n\",\"thanh to\\u00e1n th\\u1ea5t b\\u1ea1i\",\"thanh to\\u00e1n l\\u1ed7i\",\"ch\\u01b0a c\\u1eadp nh\\u1eadt thanh to\\u00e1n\"]','contains','Tình trạng thanh toán cần nhân viên xác minh. Hệ thống sẽ chuyển hội thoại sang chờ nhân viên; vui lòng không gửi mật khẩu, OTP hoặc số thẻ đầy đủ.',600,0,1,1,'always',NULL,NULL,NULL,1,0,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33','payment','payment_problem',1),(12,'Kiểm tra đơn hàng','[\"ki\\u1ec3m tra \\u0111\\u01a1n\",\"\\u0111\\u01a1n h\\u00e0ng c\\u1ee7a t\\u00f4i\",\"m\\u00e3 v\\u1eadn \\u0111\\u01a1n\",\"tra c\\u1ee9u \\u0111\\u01a1n\",\"\\u0111\\u01a1n \\u0111ang \\u1edf \\u0111\\u00e2u\"]','contains','Bạn đăng nhập và mở Đơn hàng trong tài khoản để xem trạng thái. Nếu cần hỗ trợ thêm, gửi mã đơn và chọn Yêu cầu nhân viên; thông tin đơn chỉ được cung cấp sau khi xác minh quyền sở hữu.',350,0,1,1,'always',NULL,NULL,NULL,1,0,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33','order','order',0),(13,'Thay đổi hoặc hủy đơn','[\"h\\u1ee7y \\u0111\\u01a1n\",\"hu\\u1ef7 \\u0111\\u01a1n\",\"\\u0111\\u1ed5i \\u0111\\u1ecba ch\\u1ec9\",\"s\\u1eeda \\u0111\\u1ecba ch\\u1ec9\",\"thay \\u0111\\u1ed5i \\u0111\\u01a1n\"]','contains','Yêu cầu thay đổi hoặc hủy đơn cần kiểm tra trạng thái và quyền sở hữu. Hội thoại sẽ được chuyển cho nhân viên; tin nhắn này chưa thực hiện thay đổi trên đơn hàng.',600,0,1,1,'always',NULL,NULL,NULL,1,0,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33','order','order_change',1),(14,'Sự cố giao hàng','[\"giao ch\\u1eadm\",\"ch\\u01b0a nh\\u1eadn \\u0111\\u01b0\\u1ee3c\",\"giao thi\\u1ebfu\",\"giao sai\",\"h\\u00e0ng h\\u1ecfng\",\"b\\u1ecb v\\u1ee1\",\"kh\\u00f4ng nh\\u1eadn \\u0111\\u01b0\\u1ee3c h\\u00e0ng\"]','contains','Nhân viên cần kiểm tra sự cố giao hàng. Bạn có thể gửi mã đơn và ảnh sản phẩm/kiện hàng trong hội thoại, tránh gửi thông tin nhạy cảm.',650,0,1,1,'always',NULL,NULL,NULL,1,0,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33','returns','delivery_problem',1),(15,'Đổi trả và hoàn tiền','[\"\\u0111\\u1ed5i tr\\u1ea3\",\"ho\\u00e0n ti\\u1ec1n\",\"tr\\u1ea3 h\\u00e0ng\",\"khi\\u1ebfu n\\u1ea1i\"]','contains','Nhân viên sẽ kiểm tra điều kiện đổi trả/hoàn tiền của đơn. Bạn có thể xem yêu cầu trong tài khoản; hệ thống chưa thực hiện hoàn tiền chỉ từ nội dung chat.',650,0,1,1,'always',NULL,NULL,NULL,1,0,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33','returns','returns',1),(16,'Sử dụng và bảo quản','[\"b\\u1ea3o qu\\u1ea3n\",\"v\\u1ec7 sinh\",\"c\\u00e1ch s\\u1eed d\\u1ee5ng\",\"h\\u01b0\\u1edbng d\\u1eabn s\\u1eed d\\u1ee5ng\"]','contains','Bạn xem hướng dẫn của đúng sản phẩm trên trang chi tiết. Nếu chưa rõ, gửi tên sản phẩm để nhân viên hỗ trợ; cách vệ sinh và sử dụng có thể khác nhau theo chất liệu.',220,0,1,1,'always',NULL,NULL,NULL,1,0,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33','shopping','care',0),(17,'Câu hỏi sức khỏe thú cưng','[\"b\\u1ecb b\\u1ec7nh\",\"u\\u1ed1ng thu\\u1ed1c\",\"li\\u1ec1u thu\\u1ed1c\",\"\\u0111i\\u1ec1u tr\\u1ecb\",\"n\\u00f4n \\u00f3i\"]','contains','Pawora không chẩn đoán hoặc hướng dẫn điều trị qua chat. Bạn nên liên hệ bác sĩ thú y để được đánh giá tình trạng của bé. Nhân viên chỉ hỗ trợ thông tin sản phẩm.',700,0,1,1,'always',NULL,NULL,NULL,1,0,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33','general','pet_health',0),(18,'Liên hệ nhân viên','[\"li\\u00ean h\\u1ec7 shop\",\"li\\u00ean h\\u1ec7 nh\\u00e2n vi\\u00ean\",\"c\\u1ea7n h\\u1ed7 tr\\u1ee3 tr\\u1ef1c ti\\u1ebfp\"]','contains','Hội thoại sẽ được chuyển cho nhân viên. Bạn có thể gửi thêm nội dung cần hỗ trợ tại đây.',700,0,1,1,'always',NULL,NULL,NULL,1,0,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33','general','contact',1);
/*!40000 ALTER TABLE `chat_auto_reply_rules` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `chat_conversations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_conversations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `channel_key` varchar(64) NOT NULL,
  `access_token_hash` char(64) DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `guest_name` varchar(255) NOT NULL,
  `guest_email` varchar(255) DEFAULT NULL,
  `guest_phone` varchar(30) DEFAULT NULL,
  `order_number` varchar(60) DEFAULT NULL,
  `assigned_to` bigint(20) unsigned DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'new',
  `priority` varchar(20) NOT NULL DEFAULT 'normal',
  `bot_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `customer_unread_count` int(10) unsigned NOT NULL DEFAULT 0,
  `admin_unread_count` int(10) unsigned NOT NULL DEFAULT 0,
  `last_message_at` timestamp NULL DEFAULT NULL,
  `customer_last_read_at` timestamp NULL DEFAULT NULL,
  `admin_last_read_at` timestamp NULL DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `handoff_requested_at` timestamp NULL DEFAULT NULL,
  `handoff_reason` varchar(40) DEFAULT NULL,
  `fallback_sent_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chat_conversations_uuid_unique` (`uuid`),
  UNIQUE KEY `chat_conversations_channel_key_unique` (`channel_key`),
  KEY `chat_conversations_status_last_message_at_index` (`status`,`last_message_at`),
  KEY `chat_conversations_assigned_to_status_index` (`assigned_to`,`status`),
  KEY `chat_conversations_user_id_last_message_at_index` (`user_id`,`last_message_at`),
  KEY `chat_conversations_order_number_index` (`order_number`),
  KEY `chat_conversations_handoff_requested_at_index` (`handoff_requested_at`),
  CONSTRAINT `chat_conversations_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chat_conversations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `chat_conversations` WRITE;
/*!40000 ALTER TABLE `chat_conversations` DISABLE KEYS */;
INSERT INTO `chat_conversations` VALUES (1,'5038f64c-8ae9-47b0-9a31-e6a4124cd745','b9e4af3f185c2d72a75654a285ae8adb4d99e6f51bc5c61c708dc0c5ce72ade9','dbaa8d3d3e804fe04159b29373a54040809274f16fd2f8cd9dac12df07d5f2e4',3,'Trần Minh Anh','minhanh.demo@pawora.vn','0911000101','PAW-DEMO-RV-01',NULL,'new','high',1,0,1,'2026-09-25 22:47:33',NULL,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33',NULL,NULL,NULL),(2,'5ed13103-968a-40f9-9eb1-44b9c17befa8','d3873047933decdd3eae53f4c401cac549905cfb7e45d20bc524c3c2939e600e','8cc24fb1962d94b84d46c13f551fd1470646a4baf7f33ff190d8973835059253',4,'Lê Hoàng Nam','hoangnam.demo@pawora.vn','0911000102','PAW-DEMO-RV-02',1,'open','normal',0,1,1,'2026-09-25 23:47:33',NULL,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33',NULL,NULL,NULL),(3,'880ae74f-bc50-43e3-b794-ea2aa9a2ea13','3d5e7626c2290e8171cda0dc14b157bbc380e861b2b1f421d8f9debc45a86b5d','3a43a9e37ae317c3815561e57d794fe9966f1ba9806b9e5c38d947926c3e8a50',5,'Phạm Thu Trang','thutrang.demo@pawora.vn','0911000103','PAW-DEMO-RV-03',1,'waiting_customer','normal',0,1,0,'2026-09-26 00:47:33',NULL,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33',NULL,NULL,NULL),(4,'f5168416-d445-4b60-a5e9-4ce49f565141','0ce9988f5edc5c7d522cc3a1ea59e42d13e1e3147f7e0b1644f3e651b8071ff2','306704e41edc4812787b9c36598e575bd5be8b442f53507edc1cf1da0c2709ac',6,'Nguyễn Đức Huy','duchuy.demo@pawora.vn','0911000104','PAW-DEMO-RV-04',1,'resolved','normal',0,1,0,'2026-09-26 01:47:33',NULL,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33',NULL,NULL,NULL),(5,'d3b3f569-43e8-48c9-9422-1b4e38d14ca7','c926a614d6512850194fe2b6af5aee8e4ddf8bc5ebfb60d553bfc363aff0dd43','078e71b30ee565c83c17722aea8e5c8ac8c87a3431e47cdc331ae2f0742e8585',7,'Đỗ Khánh Linh','khanhlinh.demo@pawora.vn','0911000105','PAW-DEMO-RV-05',NULL,'new','urgent',1,0,1,'2026-09-26 02:47:33',NULL,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33',NULL,NULL,NULL),(6,'81f4219b-722a-417f-9a79-19b5cde33f5f','e03297d11980484fe95d296cc47010f015c784e336b58e4c4c1f44e4b45598e8','539141755c03d4a52df70e545642a03e1ee9e085f670b7197ce329109c5d60cc',8,'Vũ Quang Minh','quangminh.demo@pawora.vn','0911000106','PAW-DEMO-RV-06',1,'open','high',0,1,1,'2026-09-26 03:47:33',NULL,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33',NULL,NULL,NULL),(7,'bffbebd8-9e45-4332-acb2-b57b700fd3f5','d4886ab096fecabc8382c927d15743d9cc1535298edba25fc833fd9d0a74e235','9c2ba885f44acfe23d264c8e0143b53bb9e11ee8e4544f9d38712f4081239eaf',9,'Bùi Ngọc Mai','ngocmai.demo@pawora.vn','0911000107','PAW-DEMO-RV-07',1,'closed','normal',0,1,0,'2026-09-26 04:47:33',NULL,NULL,'2026-09-26 06:47:33','2026-09-26 08:47:33','2026-09-26 08:47:33',NULL,NULL,NULL),(8,'43f5a731-3e80-4447-aae2-08e853c10535','ed90a1ba934e16dfecfde1cbfbdb552b148dc7db800273a749c03bfd39316d64','c2fb013741930f96b0eb7bb156b7da8122a28048d97a4b1efbb2a2f981432002',10,'Hoàng Gia Bảo','giabao.demo@pawora.vn','0911000108','PAW-DEMO-RV-08',NULL,'new','normal',1,0,1,'2026-09-26 05:47:33',NULL,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33',NULL,NULL,NULL),(9,'85decfc9-d653-4d08-993e-c06036b2ee99','efe32f7cadde7369015fd96f10e634fb2d679f4fb3cab66b2453608ed98ddb1b','8832d02594f77fdc702d7a5620491c03c52bf8048111ffb1c8fa1dba18003ce1',NULL,'Nguyễn Hải Yến','haiyen.chat@pawora.vn','0938111222',NULL,1,'waiting_customer','normal',0,1,0,'2026-09-26 06:47:33',NULL,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33',NULL,NULL,NULL),(10,'c2ae1df8-9dba-4ce8-a6de-3c2998c3cfd0','fc2c093634ddcc9ff161831ee26cb1f2fcd39223f34aeaca68adaa5acc14912f','1d734d6f0a785b41a25b92b97e560116fc0bd0e3c71753c9f4a45faa23b95262',NULL,'Demo Spam','spam.chat@pawora.vn','0900000999',NULL,NULL,'spam','normal',1,0,0,'2026-09-26 07:47:33',NULL,NULL,'2026-09-26 06:47:33','2026-09-26 08:47:33','2026-09-26 08:47:33',NULL,NULL,NULL);
/*!40000 ALTER TABLE `chat_conversations` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `chat_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` bigint(20) unsigned NOT NULL,
  `client_message_id` varchar(64) DEFAULT NULL,
  `sender_type` varchar(20) NOT NULL,
  `sender_id` bigint(20) unsigned DEFAULT NULL,
  `auto_reply_rule_id` bigint(20) unsigned DEFAULT NULL,
  `message_type` varchar(20) NOT NULL DEFAULT 'text',
  `content` text DEFAULT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `attachment_name` varchar(255) DEFAULT NULL,
  `attachment_mime` varchar(100) DEFAULT NULL,
  `attachment_size` bigint(20) unsigned DEFAULT NULL,
  `deliver_at` timestamp NULL DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `auto_reply_processed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chat_messages_client_message_id_unique` (`client_message_id`),
  KEY `chat_messages_sender_id_foreign` (`sender_id`),
  KEY `chat_messages_auto_reply_rule_id_foreign` (`auto_reply_rule_id`),
  KEY `chat_messages_conversation_id_id_index` (`conversation_id`,`id`),
  KEY `chat_messages_conversation_id_deliver_at_index` (`conversation_id`,`deliver_at`),
  CONSTRAINT `chat_messages_auto_reply_rule_id_foreign` FOREIGN KEY (`auto_reply_rule_id`) REFERENCES `chat_auto_reply_rules` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chat_messages_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `chat_conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `chat_messages` WRITE;
/*!40000 ALTER TABLE `chat_messages` DISABLE KEYS */;
INSERT INTO `chat_messages` VALUES (1,1,'pawora-demo-customer-0','customer',3,NULL,'text','Mình muốn kiểm tra đơn hàng của tôi đã có mã vận đơn chưa?',NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-25 20:47:33','2026-09-25 20:47:33',NULL,NULL,NULL),(2,1,NULL,'bot',NULL,12,'text','Bạn đăng nhập và mở Đơn hàng trong tài khoản để xem trạng thái. Nếu cần hỗ trợ thêm, gửi mã đơn và chọn Yêu cầu nhân viên; thông tin đơn chỉ được cung cấp sau khi xác minh quyền sở hữu.',NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33',NULL,NULL,NULL),(3,2,'pawora-demo-customer-1','customer',4,NULL,'text','Pawora tư vấn giúp mình chọn size yếm cho bé 9kg nhé.',NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-25 21:47:33','2026-09-25 21:47:33',NULL,NULL,NULL),(4,2,NULL,'admin',1,NULL,'text','Mình đã nhận thông tin. Bạn đo thêm vòng ngực của bé giúp Pawora nhé.',NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33',NULL,NULL,NULL),(5,3,'pawora-demo-customer-2','customer',5,NULL,'text','Sản phẩm có đổi sang màu khác được không ạ?',NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-25 22:47:33','2026-09-25 22:47:33',NULL,NULL,NULL),(6,3,NULL,'admin',1,NULL,'text','Được bạn nhé. Bạn cho Pawora biết màu muốn đổi và tình trạng sản phẩm hiện tại.',NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33',NULL,NULL,NULL),(7,4,'pawora-demo-customer-3','customer',6,NULL,'text','Mình cần hướng dẫn vệ sinh giường cho thú cưng.',NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-25 23:47:33','2026-09-25 23:47:33',NULL,NULL,NULL),(8,4,NULL,'admin',1,NULL,'text','Pawora đã gửi hướng dẫn bảo quản. Bạn có thể giặt phần vỏ ở chế độ nhẹ và phơi nơi thoáng mát.',NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33',NULL,NULL,NULL),(9,5,'pawora-demo-customer-4','customer',7,NULL,'text','Mình đã thanh toán nhưng đơn vẫn hiện đang chờ.',NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-26 00:47:33','2026-09-26 00:47:33',NULL,NULL,NULL),(10,6,'pawora-demo-customer-5','customer',8,NULL,'text','Mã vận đơn hai ngày rồi chưa cập nhật hành trình.',NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-26 01:47:33','2026-09-26 01:47:33',NULL,NULL,NULL),(11,6,NULL,'admin',1,NULL,'text','Pawora đang đối soát với đơn vị vận chuyển và sẽ cập nhật lại cho bạn sớm nhất.',NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33',NULL,NULL,NULL),(12,7,'pawora-demo-customer-6','customer',9,NULL,'text','Cảm ơn shop, mình đã nhận được hàng rồi.',NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-26 02:47:33','2026-09-26 02:47:33',NULL,NULL,NULL),(13,7,NULL,'admin',1,NULL,'text','Cảm ơn bạn đã tin chọn Pawora. Chúc bé có thật nhiều trải nghiệm vui vẻ!',NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33',NULL,NULL,NULL),(14,8,'pawora-demo-customer-7','customer',10,NULL,'text','Phí ship về Hà Nội hiện tại là bao nhiêu?',NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-26 03:47:33','2026-09-26 03:47:33',NULL,NULL,NULL),(15,8,NULL,'bot',NULL,8,'text','Pawora tính phí theo địa chỉ và giá trị đơn. Bạn chọn địa chỉ ở bước thanh toán để xem phí chính xác và điều kiện miễn phí vận chuyển đang áp dụng.',NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33',NULL,NULL,NULL),(16,9,'pawora-demo-customer-8','customer',NULL,NULL,'text','Mình muốn hỏi về chính sách hoàn tiền.',NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-26 04:47:33','2026-09-26 04:47:33',NULL,NULL,NULL),(17,9,NULL,'admin',1,NULL,'text','Bạn gửi giúp Pawora mã đơn và ảnh tình trạng sản phẩm để đội ngũ kiểm tra nhé.',NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33',NULL,NULL,NULL),(18,10,'pawora-demo-customer-9','customer',NULL,NULL,'text','Nội dung quảng cáo không liên quan.',NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-26 05:47:33','2026-09-26 05:47:33',NULL,NULL,NULL);
/*!40000 ALTER TABLE `chat_messages` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `chat_rule_triggers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chat_rule_triggers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` bigint(20) unsigned NOT NULL,
  `rule_id` bigint(20) unsigned NOT NULL,
  `source_message_id` bigint(20) unsigned NOT NULL,
  `response_message_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chat_rule_triggers_rule_id_source_message_id_unique` (`rule_id`,`source_message_id`),
  KEY `chat_rule_triggers_source_message_id_foreign` (`source_message_id`),
  KEY `chat_rule_triggers_response_message_id_foreign` (`response_message_id`),
  KEY `chat_rule_triggers_conversation_id_rule_id_index` (`conversation_id`,`rule_id`),
  CONSTRAINT `chat_rule_triggers_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `chat_conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_rule_triggers_response_message_id_foreign` FOREIGN KEY (`response_message_id`) REFERENCES `chat_messages` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chat_rule_triggers_rule_id_foreign` FOREIGN KEY (`rule_id`) REFERENCES `chat_auto_reply_rules` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_rule_triggers_source_message_id_foreign` FOREIGN KEY (`source_message_id`) REFERENCES `chat_messages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `chat_rule_triggers` WRITE;
/*!40000 ALTER TABLE `chat_rule_triggers` DISABLE KEYS */;
/*!40000 ALTER TABLE `chat_rule_triggers` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `contact_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `topic` varchar(30) NOT NULL,
  `order_number` varchar(50) DEFAULT NULL,
  `message` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'new',
  `assigned_to` bigint(20) unsigned DEFAULT NULL,
  `admin_note` text DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contact_messages_user_id_foreign` (`user_id`),
  KEY `contact_messages_assigned_to_foreign` (`assigned_to`),
  KEY `contact_messages_status_created_at_index` (`status`,`created_at`),
  KEY `contact_messages_email_index` (`email`),
  KEY `contact_messages_topic_index` (`topic`),
  KEY `contact_messages_order_number_index` (`order_number`),
  KEY `contact_messages_status_index` (`status`),
  CONSTRAINT `contact_messages_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `contact_messages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `contact_messages` WRITE;
/*!40000 ALTER TABLE `contact_messages` DISABLE KEYS */;
INSERT INTO `contact_messages` VALUES (1,NULL,'Lan Anh','lananh.contact@pawora.vn','0922000001','order',NULL,'Mình muốn kiểm tra lại thời gian giao dự kiến của đơn hàng vì tuần tới gia đình có lịch đi xa.','new',NULL,NULL,NULL,'2026-09-20 08:47:33','2026-09-21 08:47:33'),(2,NULL,'Minh Khoa','minhkhoa.contact@pawora.vn','0922000002','product',NULL,'Cho mình xin tư vấn kích thước phù hợp cho bé cún nặng khoảng tám ký và vòng ngực bốn mươi lăm centimet.','in_progress',NULL,NULL,NULL,'2026-09-21 08:47:33','2026-09-22 08:47:33'),(3,NULL,'Thu Hà','thuha.contact@pawora.vn','0922000003','returns',NULL,'Sản phẩm mình nhận còn nguyên nhưng kích thước chưa phù hợp, nhờ Pawora hướng dẫn quy trình đổi sang size khác.','resolved',NULL,'Đã hướng dẫn khách đổi kích thước.','2026-09-23 08:47:33','2026-09-22 08:47:33','2026-09-23 08:47:33'),(4,NULL,'Quốc Bảo','quocbao.contact@pawora.vn','0922000004','shipping',NULL,'Mã vận đơn đã được tạo nhưng hai ngày chưa cập nhật hành trình, nhờ đội ngũ kiểm tra giúp mình.','new',NULL,NULL,NULL,'2026-09-23 08:47:33','2026-09-24 08:47:33'),(5,NULL,'Mai Phương','maiphuong.contact@pawora.vn','0922000005','payment',NULL,'Mình đã thanh toán qua ngân hàng nhưng trạng thái đơn vẫn đang chờ, vui lòng hỗ trợ đối soát giao dịch.','new',NULL,NULL,NULL,'2026-09-24 08:47:33','2026-09-25 08:47:33'),(6,NULL,'Demo Spam','spam.contact@pawora.vn','0922000006','other',NULL,'Nội dung quảng cáo không liên quan đến sản phẩm hoặc dịch vụ chăm sóc khách hàng của Pawora.','spam',NULL,NULL,NULL,'2026-09-25 08:47:33','2026-09-26 08:47:33');
/*!40000 ALTER TABLE `contact_messages` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `coupon_usages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coupon_usages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `coupon_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `order_id` bigint(20) unsigned NOT NULL,
  `discount_amount` decimal(12,0) NOT NULL DEFAULT 0,
  `used_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `coupon_usages_order_id_unique` (`order_id`),
  KEY `coupon_usages_user_id_foreign` (`user_id`),
  KEY `coupon_usages_coupon_id_user_id_index` (`coupon_id`,`user_id`),
  CONSTRAINT `coupon_usages_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `coupon_usages_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `coupon_usages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `coupon_usages` WRITE;
/*!40000 ALTER TABLE `coupon_usages` DISABLE KEYS */;
/*!40000 ALTER TABLE `coupon_usages` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coupons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'percent',
  `value` decimal(12,0) NOT NULL,
  `minimum_order` decimal(12,0) NOT NULL DEFAULT 0,
  `maximum_discount` decimal(12,0) DEFAULT NULL,
  `starts_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `usage_limit` int(10) unsigned DEFAULT NULL,
  `per_user_limit` int(10) unsigned DEFAULT NULL,
  `first_order_only` tinyint(1) NOT NULL DEFAULT 0,
  `used_count` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_public` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `coupons_code_unique` (`code`),
  KEY `coupons_is_public_index` (`is_public`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `coupons` WRITE;
/*!40000 ALTER TABLE `coupons` DISABLE KEYS */;
INSERT INTO `coupons` VALUES (1,'WELCOME20','Chào mừng khách hàng mới','Giảm 20% cho đơn hàng đầu tiên từ 200.000đ.','percent',20,200000,150000,NULL,NULL,100,1,1,0,1,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(2,'PAWORA10','Ưu đãi thành viên Pawora','Giảm 10% cho đơn hàng đủ điều kiện.','percent',10,500000,NULL,NULL,NULL,500,3,0,0,1,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(3,'BEMOI50','Tặng bé món quà đầu tiên','Giảm trực tiếp 50.000đ cho đơn từ 300.000đ.','fixed',50000,300000,NULL,NULL,NULL,300,1,0,0,1,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(4,'FREESHIP30','Hỗ trợ phí giao hàng','Giảm 30.000đ cho đơn hàng từ 250.000đ.','fixed',30000,250000,NULL,NULL,NULL,300,2,0,0,1,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(5,'PAWORA15','Ưu đãi đơn hàng lớn',NULL,'percent',15,800000,250000,NULL,NULL,200,2,0,0,1,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(6,'GIAM100K','Giảm 100.000đ',NULL,'fixed',100000,1200000,NULL,NULL,NULL,100,1,0,0,1,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(7,'PAWORA-OLD','Ưu đãi đã kết thúc','Voucher mẫu dùng để kiểm tra trạng thái hết hạn.','percent',5,0,NULL,NULL,'2026-09-25 08:47:32',50,NULL,0,0,1,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(8,'VIP-PRIVATE','Ưu đãi riêng tư','Chỉ sử dụng khi khách hàng được Pawora cung cấp mã.','fixed',75000,500000,NULL,NULL,NULL,20,1,0,0,1,0,'2026-09-26 08:47:32','2026-09-26 08:47:32');
/*!40000 ALTER TABLE `coupons` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `inventory_adjustments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inventory_adjustments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `product_variant_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `quantity_before` int(10) unsigned NOT NULL,
  `quantity_change` int(11) NOT NULL,
  `quantity_after` int(10) unsigned NOT NULL,
  `reason` varchar(255) NOT NULL,
  `reference_type` varchar(255) DEFAULT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `inventory_adjustments_product_id_foreign` (`product_id`),
  KEY `inventory_adjustments_product_variant_id_foreign` (`product_variant_id`),
  KEY `inventory_adjustments_user_id_foreign` (`user_id`),
  KEY `inventory_adjustments_reference_type_reference_id_index` (`reference_type`,`reference_id`),
  KEY `inventory_adjustments_type_index` (`type`),
  KEY `inventory_adjustments_created_at_index` (`created_at`),
  CONSTRAINT `inventory_adjustments_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `inventory_adjustments_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inventory_adjustments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `inventory_adjustments` WRITE;
/*!40000 ALTER TABLE `inventory_adjustments` DISABLE KEYS */;
/*!40000 ALTER TABLE `inventory_adjustments` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_reset_tokens_table',1),(3,'2019_08_19_000000_create_failed_jobs_table',1),(4,'2019_12_14_000001_create_personal_access_tokens_table',1),(5,'2026_08_19_000001_create_pawora_commerce_tables',1),(6,'2026_08_19_000002_add_structured_shipping_address_to_orders',1),(7,'2026_08_19_000003_add_default_address_to_users',1),(8,'2026_08_19_000005_add_production_commerce_foundation',1),(9,'2026_08_19_000006_create_user_addresses_table',1),(10,'2026_08_21_000001_add_social_ids_to_users_table',1),(11,'2026_08_22_000001_add_avatar_path_to_users_table',1),(12,'2026_08_23_000001_add_product_variant_to_order_items',1),(13,'2026_08_27_000001_add_customer_received_at_to_shipments',1),(14,'2026_08_27_000001_create_admin_audit_logs_table',1),(15,'2026_08_27_000002_create_inventory_adjustments_table',1),(16,'2026_08_27_000003_add_admin_operational_controls',1),(17,'2026_08_29_000001_create_product_reviews_table',1),(18,'2026_08_29_000002_add_moderation_to_product_reviews',1),(19,'2026_08_29_000003_create_contact_messages_table',1),(20,'2026_08_29_000004_create_customer_chat_tables',1),(21,'2026_08_30_000001_add_structured_content_to_products',1),(22,'2026_08_30_000002_add_context_to_product_reviews',1),(23,'2026_08_31_000001_add_checkout_token_to_orders',1),(24,'2026_08_31_000001_add_shop_reply_to_product_reviews',1),(25,'2026_09_01_000001_add_idempotency_key_to_orders',1),(26,'2026_09_05_000001_add_voucher_features_and_create_coupon_usages',1),(27,'2026_09_05_000002_add_chat_handoff_and_delivery_tracking',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `product_variant_id` bigint(20) unsigned DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `sku` varchar(255) NOT NULL,
  `unit_price` decimal(12,0) NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `line_total` decimal(12,0) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_product_id_foreign` (`product_id`),
  KEY `order_items_product_variant_id_foreign` (`product_variant_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,1,1,'Bóng cao su Pounce','PAW-0001-1-1',129000,1,129000,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(2,1,5,25,'Cần câu lông vũ Miso','PAW-0005-1-1',237000,1,237000,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(3,1,9,49,'Vòng cổ AirTag Linen','PAW-0009-1-1',345000,1,345000,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(4,2,2,7,'Dây thừng Tug & Joy','PAW-0002-1-1',156000,1,156000,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(5,2,6,31,'Bóng len Moon Ball','PAW-0006-1-1',264000,1,264000,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(6,2,10,55,'Dây dắt Urban Walk','PAW-0010-1-1',372000,1,372000,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(7,3,3,13,'Đĩa bay Flexi Disc','PAW-0003-1-1',183000,1,183000,'2026-09-26 08:47:33','2026-09-26 08:47:33'),(8,3,7,37,'Bàn cào móng Cloud','PAW-0007-1-1',291000,1,291000,'2026-09-26 08:47:33','2026-09-26 08:47:33'),(9,3,11,61,'Yếm ngực Comfort Pro','PAW-0011-1-1',399000,1,399000,'2026-09-26 08:47:33','2026-09-26 08:47:33'),(10,4,4,19,'Đồ chơi phát tiếng Nibble','PAW-0004-1-1',210000,1,210000,'2026-09-26 08:47:33','2026-09-26 08:47:33'),(11,4,8,43,'Đường hầm Hide & Seek','PAW-0008-1-1',318000,1,318000,'2026-09-26 08:47:33','2026-09-26 08:47:33'),(12,4,12,67,'Bộ dây dắt Weekend','PAW-0012-1-1',426000,1,426000,'2026-09-26 08:47:33','2026-09-26 08:47:33'),(13,5,5,25,'Cần câu lông vũ Miso','PAW-0005-1-1',237000,1,237000,'2026-09-26 08:47:33','2026-09-26 08:47:33'),(14,5,9,49,'Vòng cổ AirTag Linen','PAW-0009-1-1',345000,1,345000,'2026-09-26 08:47:33','2026-09-26 08:47:33'),(15,5,13,73,'Giường ngủ Cocoon','PAW-0013-1-1',453000,1,453000,'2026-09-26 08:47:33','2026-09-26 08:47:33'),(16,6,6,31,'Bóng len Moon Ball','PAW-0006-1-1',264000,1,264000,'2026-09-26 08:47:33','2026-09-26 08:47:33'),(17,6,10,55,'Dây dắt Urban Walk','PAW-0010-1-1',372000,1,372000,'2026-09-26 08:47:33','2026-09-26 08:47:33'),(18,6,14,79,'Nệm mây Soft Nest','PAW-0014-1-1',480000,1,480000,'2026-09-26 08:47:33','2026-09-26 08:47:33'),(19,7,7,37,'Bàn cào móng Cloud','PAW-0007-1-1',291000,1,291000,'2026-09-26 08:47:33','2026-09-26 08:47:33'),(20,7,11,61,'Yếm ngực Comfort Pro','PAW-0011-1-1',399000,1,399000,'2026-09-26 08:47:33','2026-09-26 08:47:33'),(21,7,15,85,'Chăn cotton Calm','PAW-0015-1-1',507000,1,507000,'2026-09-26 08:47:33','2026-09-26 08:47:33'),(22,8,8,43,'Đường hầm Hide & Seek','PAW-0008-1-1',318000,1,318000,'2026-09-26 08:47:33','2026-09-26 08:47:33'),(23,8,12,67,'Bộ dây dắt Weekend','PAW-0012-1-1',426000,1,426000,'2026-09-26 08:47:33','2026-09-26 08:47:33'),(24,8,16,91,'Nhà vải Hideaway','PAW-0016-1-1',534000,1,534000,'2026-09-26 08:47:33','2026-09-26 08:47:33');
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `order_status_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_status_histories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `from_status` varchar(255) DEFAULT NULL,
  `to_status` varchar(255) NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_status_histories_order_id_foreign` (`order_id`),
  KEY `order_status_histories_user_id_foreign` (`user_id`),
  CONSTRAINT `order_status_histories_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_status_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `order_status_histories` WRITE;
/*!40000 ALTER TABLE `order_status_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_status_histories` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `number` varchar(255) NOT NULL,
  `public_token` char(36) DEFAULT NULL,
  `idempotency_key` char(36) DEFAULT NULL,
  `checkout_token` char(36) DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `province` varchar(255) DEFAULT NULL,
  `district` varchar(255) DEFAULT NULL,
  `ward` varchar(255) DEFAULT NULL,
  `address_line` varchar(255) DEFAULT NULL,
  `shipping_address` text NOT NULL,
  `note` text DEFAULT NULL,
  `coupon_code` varchar(255) DEFAULT NULL,
  `subtotal` decimal(12,0) NOT NULL,
  `discount` decimal(12,0) NOT NULL DEFAULT 0,
  `shipping_fee` decimal(12,0) NOT NULL DEFAULT 0,
  `shipping_zone` varchar(255) DEFAULT NULL,
  `estimated_delivery` varchar(255) DEFAULT NULL,
  `total` decimal(12,0) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'VND',
  `payment_method` varchar(255) NOT NULL DEFAULT 'cod',
  `payment_status` varchar(255) NOT NULL DEFAULT 'unpaid',
  `paid_at` timestamp NULL DEFAULT NULL,
  `refunded_amount` decimal(12,0) NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_number_unique` (`number`),
  UNIQUE KEY `orders_public_token_unique` (`public_token`),
  UNIQUE KEY `orders_checkout_token_unique` (`checkout_token`),
  UNIQUE KEY `orders_idempotency_key_unique` (`idempotency_key`),
  KEY `orders_user_id_foreign` (`user_id`),
  KEY `orders_payment_status_index` (`payment_status`),
  KEY `orders_status_index` (`status`),
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,'PAW-DEMO-RV-01',NULL,NULL,NULL,3,'Trần Minh Anh','minhanh.demo@pawora.vn','0911000101',NULL,NULL,NULL,NULL,'18, đường Pawora, Phường Tây Tựu, Thành phố Hà Nội',NULL,NULL,711000,0,0,NULL,NULL,711000,'VND','cod','paid','2026-05-29 08:47:32',0,'delivered','2026-05-25 08:47:32','2026-05-31 08:47:32'),(2,'PAW-DEMO-RV-02',NULL,NULL,NULL,4,'Lê Hoàng Nam','hoangnam.demo@pawora.vn','0911000102',NULL,NULL,NULL,NULL,'19, đường Pawora, Phường Bến Thành, Thành phố Hồ Chí Minh',NULL,NULL,792000,0,0,NULL,NULL,792000,'VND','cod','paid','2026-05-28 08:47:32',0,'delivered','2026-05-24 08:47:32','2026-05-30 08:47:32'),(3,'PAW-DEMO-RV-03',NULL,NULL,NULL,5,'Phạm Thu Trang','thutrang.demo@pawora.vn','0911000103',NULL,NULL,NULL,NULL,'20, đường Pawora, Phường Tây Tựu, Thành phố Hà Nội',NULL,NULL,873000,0,0,NULL,NULL,873000,'VND','cod','paid','2026-05-27 08:47:33',0,'delivered','2026-05-23 08:47:33','2026-05-29 08:47:33'),(4,'PAW-DEMO-RV-04',NULL,NULL,NULL,6,'Nguyễn Đức Huy','duchuy.demo@pawora.vn','0911000104',NULL,NULL,NULL,NULL,'21, đường Pawora, Phường Bến Thành, Thành phố Hồ Chí Minh',NULL,NULL,954000,0,0,NULL,NULL,954000,'VND','cod','paid','2026-05-26 08:47:33',0,'delivered','2026-05-22 08:47:33','2026-05-28 08:47:33'),(5,'PAW-DEMO-RV-05',NULL,NULL,NULL,7,'Đỗ Khánh Linh','khanhlinh.demo@pawora.vn','0911000105',NULL,NULL,NULL,NULL,'22, đường Pawora, Phường Tây Tựu, Thành phố Hà Nội',NULL,NULL,1035000,0,0,NULL,NULL,1035000,'VND','cod','paid','2026-05-25 08:47:33',0,'delivered','2026-05-21 08:47:33','2026-05-27 08:47:33'),(6,'PAW-DEMO-RV-06',NULL,NULL,NULL,8,'Vũ Quang Minh','quangminh.demo@pawora.vn','0911000106',NULL,NULL,NULL,NULL,'23, đường Pawora, Phường Bến Thành, Thành phố Hồ Chí Minh',NULL,NULL,1116000,0,0,NULL,NULL,1116000,'VND','cod','paid','2026-05-24 08:47:33',0,'delivered','2026-05-20 08:47:33','2026-05-26 08:47:33'),(7,'PAW-DEMO-RV-07',NULL,NULL,NULL,9,'Bùi Ngọc Mai','ngocmai.demo@pawora.vn','0911000107',NULL,NULL,NULL,NULL,'24, đường Pawora, Phường Tây Tựu, Thành phố Hà Nội',NULL,NULL,1197000,0,0,NULL,NULL,1197000,'VND','cod','paid','2026-05-23 08:47:33',0,'delivered','2026-05-19 08:47:33','2026-05-25 08:47:33'),(8,'PAW-DEMO-RV-08',NULL,NULL,NULL,10,'Hoàng Gia Bảo','giabao.demo@pawora.vn','0911000108',NULL,NULL,NULL,NULL,'25, đường Pawora, Phường Bến Thành, Thành phố Hồ Chí Minh',NULL,NULL,1278000,0,0,NULL,NULL,1278000,'VND','cod','paid','2026-05-22 08:47:33',0,'delivered','2026-05-18 08:47:33','2026-05-24 08:47:33');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `payment_transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `request_id` char(36) NOT NULL,
  `provider` varchar(255) NOT NULL,
  `provider_transaction_id` varchar(255) DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'payment',
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `amount` decimal(12,0) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'VND',
  `request_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`request_payload`)),
  `response_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`response_payload`)),
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_transactions_request_id_unique` (`request_id`),
  UNIQUE KEY `payment_transactions_provider_provider_transaction_id_unique` (`provider`,`provider_transaction_id`),
  KEY `payment_transactions_order_id_foreign` (`order_id`),
  KEY `payment_transactions_provider_index` (`provider`),
  KEY `payment_transactions_provider_transaction_id_index` (`provider_transaction_id`),
  KEY `payment_transactions_status_index` (`status`),
  CONSTRAINT `payment_transactions_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `payment_transactions` WRITE;
/*!40000 ALTER TABLE `payment_transactions` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_transactions` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `product_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_reviews` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `order_id` bigint(20) unsigned DEFAULT NULL,
  `rating` tinyint(3) unsigned NOT NULL,
  `fit` varchar(30) DEFAULT NULL,
  `pet_weight` decimal(5,2) DEFAULT NULL,
  `purchased_variant` varchar(160) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `comment` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'approved',
  `admin_note` text DEFAULT NULL,
  `shop_reply` text DEFAULT NULL,
  `replied_by` bigint(20) unsigned DEFAULT NULL,
  `replied_at` timestamp NULL DEFAULT NULL,
  `moderated_by` bigint(20) unsigned DEFAULT NULL,
  `moderated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_reviews_product_id_user_id_unique` (`product_id`,`user_id`),
  KEY `product_reviews_user_id_foreign` (`user_id`),
  KEY `product_reviews_order_id_foreign` (`order_id`),
  KEY `product_reviews_moderated_by_foreign` (`moderated_by`),
  KEY `product_reviews_status_index` (`status`),
  KEY `product_reviews_product_id_status_created_at_index` (`product_id`,`status`,`created_at`),
  KEY `product_reviews_replied_by_foreign` (`replied_by`),
  CONSTRAINT `product_reviews_moderated_by_foreign` FOREIGN KEY (`moderated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `product_reviews_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `product_reviews_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_reviews_replied_by_foreign` FOREIGN KEY (`replied_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `product_reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `product_reviews` WRITE;
/*!40000 ALTER TABLE `product_reviews` DISABLE KEYS */;
INSERT INTO `product_reviews` VALUES (1,1,3,1,5,'small',3.50,'Xanh lá · Nhỏ','Bé nhà mình rất thích','Sản phẩm hoàn thiện tốt, bề mặt dễ vệ sinh và bé nhà mình làm quen rất nhanh sau khi mở hộp.','approved',NULL,'Cảm ơn bạn đã chia sẻ trải nghiệm. Pawora rất vui khi sản phẩm phù hợp với bé!',1,'2026-07-28 08:47:32',1,'2026-07-28 08:47:32','2026-07-03 08:47:32','2026-07-04 08:47:32'),(2,5,3,1,4,'true_to_size',4.30,'Hồng · Nhỏ','Chất liệu chắc chắn','Mình đã dùng hơn hai tuần, đường may và các chi tiết vẫn chắc chắn, màu sắc giống hình trên trang.','approved',NULL,NULL,NULL,NULL,1,'2026-07-27 08:47:32','2026-07-04 08:47:32','2026-07-05 08:47:32'),(3,9,3,1,5,'large',5.10,'Be · S','Đúng mô tả và dễ dùng','Kích thước đúng với phần mô tả, thao tác sử dụng đơn giản và không gây khó chịu cho thú cưng.','approved',NULL,NULL,NULL,NULL,1,'2026-07-26 08:47:32','2026-07-05 08:47:32','2026-07-06 08:47:32'),(4,2,4,2,4,'true_to_size',4.30,'Xanh lá · Nhỏ','Chất liệu chắc chắn','Mình đã dùng hơn hai tuần, đường may và các chi tiết vẫn chắc chắn, màu sắc giống hình trên trang.','approved',NULL,NULL,NULL,NULL,1,'2026-07-25 08:47:32','2026-07-06 08:47:32','2026-07-07 08:47:32'),(5,6,4,2,5,'large',5.10,'Hồng · Nhỏ','Đúng mô tả và dễ dùng','Kích thước đúng với phần mô tả, thao tác sử dụng đơn giản và không gây khó chịu cho thú cưng.','approved',NULL,'Cảm ơn bạn đã chia sẻ trải nghiệm. Pawora rất vui khi sản phẩm phù hợp với bé!',1,'2026-07-24 08:47:32',1,'2026-07-24 08:47:32','2026-07-07 08:47:32','2026-07-08 08:47:32'),(6,10,4,2,4,'small',5.90,'Be · S','Thiết kế đẹp, đóng gói kỹ','Pawora đóng gói cẩn thận, sản phẩm không có mùi lạ và có hướng dẫn bảo quản khá rõ ràng.','approved',NULL,NULL,NULL,NULL,1,'2026-07-23 08:47:32','2026-07-08 08:47:32','2026-07-09 08:47:32'),(7,3,5,3,5,'large',5.10,'Xanh lá · Nhỏ','Đúng mô tả và dễ dùng','Kích thước đúng với phần mô tả, thao tác sử dụng đơn giản và không gây khó chịu cho thú cưng.','approved',NULL,NULL,NULL,NULL,1,'2026-07-22 08:47:33','2026-07-09 08:47:33','2026-07-10 08:47:33'),(8,7,5,3,4,'small',5.90,'Hồng · Nhỏ','Thiết kế đẹp, đóng gói kỹ','Pawora đóng gói cẩn thận, sản phẩm không có mùi lạ và có hướng dẫn bảo quản khá rõ ràng.','approved',NULL,NULL,NULL,NULL,1,'2026-07-21 08:47:33','2026-07-10 08:47:33','2026-07-11 08:47:33'),(9,11,5,3,5,'true_to_size',6.70,'Be · S','Phù hợp dùng hằng ngày','Thiết kế gọn, đặt trong nhà không chiếm nhiều diện tích và chất liệu cho cảm giác bền khi sử dụng.','approved',NULL,'Cảm ơn bạn đã chia sẻ trải nghiệm. Pawora rất vui khi sản phẩm phù hợp với bé!',1,'2026-07-20 08:47:33',1,'2026-07-20 08:47:33','2026-07-11 08:47:33','2026-07-12 08:47:33'),(10,4,6,4,4,'small',5.90,'Xanh lá · Nhỏ','Thiết kế đẹp, đóng gói kỹ','Pawora đóng gói cẩn thận, sản phẩm không có mùi lạ và có hướng dẫn bảo quản khá rõ ràng.','approved',NULL,NULL,NULL,NULL,1,'2026-07-19 08:47:33','2026-07-12 08:47:33','2026-07-13 08:47:33'),(11,8,6,4,5,'true_to_size',6.70,'Hồng · Nhỏ','Phù hợp dùng hằng ngày','Thiết kế gọn, đặt trong nhà không chiếm nhiều diện tích và chất liệu cho cảm giác bền khi sử dụng.','approved',NULL,NULL,NULL,NULL,1,'2026-07-18 08:47:33','2026-07-13 08:47:33','2026-07-14 08:47:33'),(12,12,6,4,3,'large',7.50,'Be · S','Kích thước vừa vặn','Mình chọn theo bảng kích thước và nhận được sản phẩm vừa vặn, bé vận động vẫn khá thoải mái.','hidden','Tạm ẩn để kiểm tra thông tin mô tả.',NULL,NULL,NULL,1,'2026-07-17 08:47:33','2026-07-14 08:47:33','2026-07-15 08:47:33'),(13,5,7,5,5,'true_to_size',6.70,'Hồng · Nhỏ','Phù hợp dùng hằng ngày','Thiết kế gọn, đặt trong nhà không chiếm nhiều diện tích và chất liệu cho cảm giác bền khi sử dụng.','approved',NULL,'Cảm ơn bạn đã chia sẻ trải nghiệm. Pawora rất vui khi sản phẩm phù hợp với bé!',1,'2026-07-16 08:47:33',1,'2026-07-16 08:47:33','2026-07-15 08:47:33','2026-07-16 08:47:33'),(14,9,7,5,3,'large',7.50,'Be · S','Kích thước vừa vặn','Mình chọn theo bảng kích thước và nhận được sản phẩm vừa vặn, bé vận động vẫn khá thoải mái.','approved',NULL,NULL,NULL,NULL,1,'2026-07-15 08:47:33','2026-07-16 08:47:33','2026-07-17 08:47:33'),(15,13,7,5,4,'small',8.30,'Kem · S','Trải nghiệm ở mức khá','Chất lượng nhìn chung ổn nhưng màu thực tế trầm hơn ảnh một chút, phần còn lại đúng như mô tả.','approved',NULL,NULL,NULL,NULL,1,'2026-07-14 08:47:33','2026-07-17 08:47:33','2026-07-18 08:47:33'),(16,6,8,6,3,'large',7.50,'Hồng · Nhỏ','Kích thước vừa vặn','Mình chọn theo bảng kích thước và nhận được sản phẩm vừa vặn, bé vận động vẫn khá thoải mái.','approved',NULL,NULL,NULL,NULL,1,'2026-07-13 08:47:33','2026-07-18 08:47:33','2026-07-19 08:47:33'),(17,10,8,6,4,'small',8.30,'Be · S','Trải nghiệm ở mức khá','Chất lượng nhìn chung ổn nhưng màu thực tế trầm hơn ảnh một chút, phần còn lại đúng như mô tả.','approved',NULL,'Cảm ơn bạn đã chia sẻ trải nghiệm. Pawora rất vui khi sản phẩm phù hợp với bé!',1,'2026-07-12 08:47:33',1,'2026-07-12 08:47:33','2026-07-19 08:47:33','2026-07-20 08:47:33'),(18,14,8,6,2,'true_to_size',9.10,'Kem · S','Dễ vệ sinh hơn mình nghĩ','Sau vài lần vệ sinh sản phẩm vẫn giữ được dáng, đây là điểm mình hài lòng nhất khi sử dụng.','approved',NULL,NULL,NULL,NULL,1,'2026-07-11 08:47:33','2026-07-20 08:47:33','2026-07-21 08:47:33'),(19,7,9,7,4,'small',8.30,'Hồng · Nhỏ','Trải nghiệm ở mức khá','Chất lượng nhìn chung ổn nhưng màu thực tế trầm hơn ảnh một chút, phần còn lại đúng như mô tả.','approved',NULL,NULL,NULL,NULL,1,'2026-07-10 08:47:33','2026-07-21 08:47:33','2026-07-22 08:47:33'),(20,11,9,7,2,'true_to_size',9.10,'Be · S','Dễ vệ sinh hơn mình nghĩ','Sau vài lần vệ sinh sản phẩm vẫn giữ được dáng, đây là điểm mình hài lòng nhất khi sử dụng.','hidden','Tạm ẩn để kiểm tra thông tin mô tả.',NULL,NULL,NULL,1,'2026-07-09 08:47:33','2026-07-22 08:47:33','2026-07-23 08:47:33'),(21,15,9,7,5,'small',9.90,'Kem · S','Bé nhà mình rất thích','Sản phẩm hoàn thiện tốt, bề mặt dễ vệ sinh và bé nhà mình làm quen rất nhanh sau khi mở hộp.','approved',NULL,'Cảm ơn bạn đã chia sẻ trải nghiệm. Pawora rất vui khi sản phẩm phù hợp với bé!',1,'2026-07-08 08:47:33',1,'2026-07-08 08:47:33','2026-07-23 08:47:33','2026-07-24 08:47:33'),(22,8,10,8,2,'true_to_size',9.10,'Hồng · Nhỏ','Dễ vệ sinh hơn mình nghĩ','Sau vài lần vệ sinh sản phẩm vẫn giữ được dáng, đây là điểm mình hài lòng nhất khi sử dụng.','approved',NULL,NULL,NULL,NULL,1,'2026-07-07 08:47:33','2026-07-24 08:47:33','2026-07-25 08:47:33'),(23,12,10,8,5,'small',9.90,'Be · S','Bé nhà mình rất thích','Sản phẩm hoàn thiện tốt, bề mặt dễ vệ sinh và bé nhà mình làm quen rất nhanh sau khi mở hộp.','approved',NULL,NULL,NULL,NULL,1,'2026-07-06 08:47:33','2026-07-25 08:47:33','2026-07-26 08:47:33'),(24,16,10,8,4,'true_to_size',10.70,'Kem · S','Chất liệu chắc chắn','Mình đã dùng hơn hai tuần, đường may và các chi tiết vẫn chắc chắn, màu sắc giống hình trên trang.','hidden','Tạm ẩn để kiểm tra thông tin mô tả.',NULL,NULL,NULL,1,'2026-07-05 08:47:33','2026-07-26 08:47:33','2026-07-27 08:47:33');
/*!40000 ALTER TABLE `product_reviews` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `product_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_variants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `sku` varchar(255) NOT NULL,
  `size` varchar(255) DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL,
  `price` decimal(12,0) DEFAULT NULL,
  `stock` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_variants_sku_unique` (`sku`),
  KEY `product_variants_product_id_foreign` (`product_id`),
  KEY `product_variants_is_active_index` (`is_active`),
  CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=193 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `product_variants` WRITE;
/*!40000 ALTER TABLE `product_variants` DISABLE KEYS */;
INSERT INTO `product_variants` VALUES (1,1,'Xanh lá · Nhỏ','PAW-0001-1-1','Nhỏ','Xanh lá',129000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(2,1,'Xanh lá · Lớn','PAW-0001-1-2','Lớn','Xanh lá',159000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(3,1,'Vàng · Nhỏ','PAW-0001-2-1','Nhỏ','Vàng',129000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(4,1,'Vàng · Lớn','PAW-0001-2-2','Lớn','Vàng',159000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(5,1,'Cam · Nhỏ','PAW-0001-3-1','Nhỏ','Cam',129000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(6,1,'Cam · Lớn','PAW-0001-3-2','Lớn','Cam',159000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(7,2,'Xanh lá · Nhỏ','PAW-0002-1-1','Nhỏ','Xanh lá',156000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(8,2,'Xanh lá · Lớn','PAW-0002-1-2','Lớn','Xanh lá',186000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(9,2,'Vàng · Nhỏ','PAW-0002-2-1','Nhỏ','Vàng',156000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(10,2,'Vàng · Lớn','PAW-0002-2-2','Lớn','Vàng',186000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(11,2,'Cam · Nhỏ','PAW-0002-3-1','Nhỏ','Cam',156000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(12,2,'Cam · Lớn','PAW-0002-3-2','Lớn','Cam',186000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(13,3,'Xanh lá · Nhỏ','PAW-0003-1-1','Nhỏ','Xanh lá',183000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(14,3,'Xanh lá · Lớn','PAW-0003-1-2','Lớn','Xanh lá',213000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(15,3,'Vàng · Nhỏ','PAW-0003-2-1','Nhỏ','Vàng',183000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(16,3,'Vàng · Lớn','PAW-0003-2-2','Lớn','Vàng',213000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(17,3,'Cam · Nhỏ','PAW-0003-3-1','Nhỏ','Cam',183000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(18,3,'Cam · Lớn','PAW-0003-3-2','Lớn','Cam',213000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(19,4,'Xanh lá · Nhỏ','PAW-0004-1-1','Nhỏ','Xanh lá',210000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(20,4,'Xanh lá · Lớn','PAW-0004-1-2','Lớn','Xanh lá',240000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(21,4,'Vàng · Nhỏ','PAW-0004-2-1','Nhỏ','Vàng',210000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(22,4,'Vàng · Lớn','PAW-0004-2-2','Lớn','Vàng',240000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(23,4,'Cam · Nhỏ','PAW-0004-3-1','Nhỏ','Cam',210000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(24,4,'Cam · Lớn','PAW-0004-3-2','Lớn','Cam',240000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(25,5,'Hồng · Nhỏ','PAW-0005-1-1','Nhỏ','Hồng',237000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(26,5,'Hồng · Lớn','PAW-0005-1-2','Lớn','Hồng',267000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(27,5,'Xanh dương · Nhỏ','PAW-0005-2-1','Nhỏ','Xanh dương',237000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(28,5,'Xanh dương · Lớn','PAW-0005-2-2','Lớn','Xanh dương',267000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(29,5,'Tím · Nhỏ','PAW-0005-3-1','Nhỏ','Tím',237000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(30,5,'Tím · Lớn','PAW-0005-3-2','Lớn','Tím',267000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(31,6,'Hồng · Nhỏ','PAW-0006-1-1','Nhỏ','Hồng',264000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(32,6,'Hồng · Lớn','PAW-0006-1-2','Lớn','Hồng',294000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(33,6,'Xanh dương · Nhỏ','PAW-0006-2-1','Nhỏ','Xanh dương',264000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(34,6,'Xanh dương · Lớn','PAW-0006-2-2','Lớn','Xanh dương',294000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(35,6,'Tím · Nhỏ','PAW-0006-3-1','Nhỏ','Tím',264000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(36,6,'Tím · Lớn','PAW-0006-3-2','Lớn','Tím',294000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(37,7,'Hồng · Nhỏ','PAW-0007-1-1','Nhỏ','Hồng',291000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(38,7,'Hồng · Lớn','PAW-0007-1-2','Lớn','Hồng',321000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(39,7,'Xanh dương · Nhỏ','PAW-0007-2-1','Nhỏ','Xanh dương',291000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(40,7,'Xanh dương · Lớn','PAW-0007-2-2','Lớn','Xanh dương',321000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(41,7,'Tím · Nhỏ','PAW-0007-3-1','Nhỏ','Tím',291000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(42,7,'Tím · Lớn','PAW-0007-3-2','Lớn','Tím',321000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(43,8,'Hồng · Nhỏ','PAW-0008-1-1','Nhỏ','Hồng',318000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(44,8,'Hồng · Lớn','PAW-0008-1-2','Lớn','Hồng',348000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(45,8,'Xanh dương · Nhỏ','PAW-0008-2-1','Nhỏ','Xanh dương',318000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(46,8,'Xanh dương · Lớn','PAW-0008-2-2','Lớn','Xanh dương',348000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(47,8,'Tím · Nhỏ','PAW-0008-3-1','Nhỏ','Tím',318000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(48,8,'Tím · Lớn','PAW-0008-3-2','Lớn','Tím',348000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(49,9,'Be · S','PAW-0009-1-1','S','Be',345000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(50,9,'Be · M','PAW-0009-1-2','M','Be',375000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(51,9,'Đen · S','PAW-0009-2-1','S','Đen',345000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(52,9,'Đen · M','PAW-0009-2-2','M','Đen',375000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(53,9,'Xanh navy · S','PAW-0009-3-1','S','Xanh navy',345000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(54,9,'Xanh navy · M','PAW-0009-3-2','M','Xanh navy',375000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(55,10,'Be · S','PAW-0010-1-1','S','Be',372000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(56,10,'Be · M','PAW-0010-1-2','M','Be',402000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(57,10,'Đen · S','PAW-0010-2-1','S','Đen',372000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(58,10,'Đen · M','PAW-0010-2-2','M','Đen',402000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(59,10,'Xanh navy · S','PAW-0010-3-1','S','Xanh navy',372000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(60,10,'Xanh navy · M','PAW-0010-3-2','M','Xanh navy',402000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(61,11,'Be · S','PAW-0011-1-1','S','Be',399000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(62,11,'Be · M','PAW-0011-1-2','M','Be',429000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(63,11,'Đen · S','PAW-0011-2-1','S','Đen',399000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(64,11,'Đen · M','PAW-0011-2-2','M','Đen',429000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(65,11,'Xanh navy · S','PAW-0011-3-1','S','Xanh navy',399000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(66,11,'Xanh navy · M','PAW-0011-3-2','M','Xanh navy',429000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(67,12,'Be · S','PAW-0012-1-1','S','Be',426000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(68,12,'Be · M','PAW-0012-1-2','M','Be',456000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(69,12,'Đen · S','PAW-0012-2-1','S','Đen',426000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(70,12,'Đen · M','PAW-0012-2-2','M','Đen',456000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(71,12,'Xanh navy · S','PAW-0012-3-1','S','Xanh navy',426000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(72,12,'Xanh navy · M','PAW-0012-3-2','M','Xanh navy',456000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(73,13,'Kem · S','PAW-0013-1-1','S','Kem',453000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(74,13,'Kem · M','PAW-0013-1-2','M','Kem',483000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(75,13,'Xám · S','PAW-0013-2-1','S','Xám',453000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(76,13,'Xám · M','PAW-0013-2-2','M','Xám',483000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(77,13,'Hồng · S','PAW-0013-3-1','S','Hồng',453000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(78,13,'Hồng · M','PAW-0013-3-2','M','Hồng',483000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(79,14,'Kem · S','PAW-0014-1-1','S','Kem',480000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(80,14,'Kem · M','PAW-0014-1-2','M','Kem',510000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(81,14,'Xám · S','PAW-0014-2-1','S','Xám',480000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(82,14,'Xám · M','PAW-0014-2-2','M','Xám',510000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(83,14,'Hồng · S','PAW-0014-3-1','S','Hồng',480000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(84,14,'Hồng · M','PAW-0014-3-2','M','Hồng',510000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(85,15,'Kem · S','PAW-0015-1-1','S','Kem',507000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(86,15,'Kem · M','PAW-0015-1-2','M','Kem',537000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(87,15,'Xám · S','PAW-0015-2-1','S','Xám',507000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(88,15,'Xám · M','PAW-0015-2-2','M','Xám',537000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(89,15,'Hồng · S','PAW-0015-3-1','S','Hồng',507000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(90,15,'Hồng · M','PAW-0015-3-2','M','Hồng',537000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(91,16,'Kem · S','PAW-0016-1-1','S','Kem',534000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(92,16,'Kem · M','PAW-0016-1-2','M','Kem',564000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(93,16,'Xám · S','PAW-0016-2-1','S','Xám',534000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(94,16,'Xám · M','PAW-0016-2-2','M','Xám',564000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(95,16,'Hồng · S','PAW-0016-3-1','S','Hồng',534000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(96,16,'Hồng · M','PAW-0016-3-2','M','Hồng',564000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(97,17,'Trắng · 350 ml','PAW-0017-1-1','350 ml','Trắng',561000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(98,17,'Trắng · 550 ml','PAW-0017-1-2','550 ml','Trắng',591000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(99,17,'Xanh mint · 350 ml','PAW-0017-2-1','350 ml','Xanh mint',561000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(100,17,'Xanh mint · 550 ml','PAW-0017-2-2','550 ml','Xanh mint',591000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(101,17,'Hồng · 350 ml','PAW-0017-3-1','350 ml','Hồng',561000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(102,17,'Hồng · 550 ml','PAW-0017-3-2','550 ml','Hồng',591000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(103,18,'Trắng · 350 ml','PAW-0018-1-1','350 ml','Trắng',588000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(104,18,'Trắng · 550 ml','PAW-0018-1-2','550 ml','Trắng',618000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(105,18,'Xanh mint · 350 ml','PAW-0018-2-1','350 ml','Xanh mint',588000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(106,18,'Xanh mint · 550 ml','PAW-0018-2-2','550 ml','Xanh mint',618000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(107,18,'Hồng · 350 ml','PAW-0018-3-1','350 ml','Hồng',588000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(108,18,'Hồng · 550 ml','PAW-0018-3-2','550 ml','Hồng',618000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(109,19,'Trắng · 350 ml','PAW-0019-1-1','350 ml','Trắng',615000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(110,19,'Trắng · 550 ml','PAW-0019-1-2','550 ml','Trắng',645000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(111,19,'Xanh mint · 350 ml','PAW-0019-2-1','350 ml','Xanh mint',615000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(112,19,'Xanh mint · 550 ml','PAW-0019-2-2','550 ml','Xanh mint',645000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(113,19,'Hồng · 350 ml','PAW-0019-3-1','350 ml','Hồng',615000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(114,19,'Hồng · 550 ml','PAW-0019-3-2','550 ml','Hồng',645000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(115,20,'Trắng · 1,3 lít','PAW-0020-1-1','1,3 lít','Trắng',642000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(116,20,'Trắng · 2 lít','PAW-0020-1-2','2 lít','Trắng',672000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(117,20,'Xanh mint · 1,3 lít','PAW-0020-2-1','1,3 lít','Xanh mint',642000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(118,20,'Xanh mint · 2 lít','PAW-0020-2-2','2 lít','Xanh mint',672000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(119,20,'Hồng · 1,3 lít','PAW-0020-3-1','1,3 lít','Hồng',642000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(120,20,'Hồng · 2 lít','PAW-0020-3-2','2 lít','Hồng',672000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(121,21,'Trắng · Tiêu chuẩn','PAW-0021-1-1','Tiêu chuẩn','Trắng',669000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(122,21,'Trắng · Lớn','PAW-0021-1-2','Lớn','Trắng',699000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(123,21,'Xanh dương · Tiêu chuẩn','PAW-0021-2-1','Tiêu chuẩn','Xanh dương',669000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(124,21,'Xanh dương · Lớn','PAW-0021-2-2','Lớn','Xanh dương',699000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(125,21,'Hồng · Tiêu chuẩn','PAW-0021-3-1','Tiêu chuẩn','Hồng',669000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(126,21,'Hồng · Lớn','PAW-0021-3-2','Lớn','Hồng',699000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(127,22,'Trắng · Tiêu chuẩn','PAW-0022-1-1','Tiêu chuẩn','Trắng',696000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(128,22,'Trắng · Lớn','PAW-0022-1-2','Lớn','Trắng',726000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(129,22,'Xanh dương · Tiêu chuẩn','PAW-0022-2-1','Tiêu chuẩn','Xanh dương',696000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(130,22,'Xanh dương · Lớn','PAW-0022-2-2','Lớn','Xanh dương',726000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(131,22,'Hồng · Tiêu chuẩn','PAW-0022-3-1','Tiêu chuẩn','Hồng',696000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(132,22,'Hồng · Lớn','PAW-0022-3-2','Lớn','Hồng',726000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(133,23,'Trắng · Tiêu chuẩn','PAW-0023-1-1','Tiêu chuẩn','Trắng',723000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(134,23,'Trắng · Lớn','PAW-0023-1-2','Lớn','Trắng',753000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(135,23,'Xanh dương · Tiêu chuẩn','PAW-0023-2-1','Tiêu chuẩn','Xanh dương',723000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(136,23,'Xanh dương · Lớn','PAW-0023-2-2','Lớn','Xanh dương',753000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(137,23,'Hồng · Tiêu chuẩn','PAW-0023-3-1','Tiêu chuẩn','Hồng',723000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(138,23,'Hồng · Lớn','PAW-0023-3-2','Lớn','Hồng',753000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(139,24,'Trắng · Tiêu chuẩn','PAW-0024-1-1','Tiêu chuẩn','Trắng',750000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(140,24,'Trắng · Lớn','PAW-0024-1-2','Lớn','Trắng',780000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(141,24,'Xanh dương · Tiêu chuẩn','PAW-0024-2-1','Tiêu chuẩn','Xanh dương',750000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(142,24,'Xanh dương · Lớn','PAW-0024-2-2','Lớn','Xanh dương',780000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(143,24,'Hồng · Tiêu chuẩn','PAW-0024-3-1','Tiêu chuẩn','Hồng',750000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(144,24,'Hồng · Lớn','PAW-0024-3-2','Lớn','Hồng',780000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(145,25,'Kem · S','PAW-0025-1-1','S','Kem',777000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(146,25,'Kem · M','PAW-0025-1-2','M','Kem',807000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(147,25,'Xanh navy · S','PAW-0025-2-1','S','Xanh navy',777000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(148,25,'Xanh navy · M','PAW-0025-2-2','M','Xanh navy',807000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(149,25,'Đỏ · S','PAW-0025-3-1','S','Đỏ',777000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(150,25,'Đỏ · M','PAW-0025-3-2','M','Đỏ',807000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(151,26,'Kem · S','PAW-0026-1-1','S','Kem',804000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(152,26,'Kem · M','PAW-0026-1-2','M','Kem',834000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(153,26,'Xanh navy · S','PAW-0026-2-1','S','Xanh navy',804000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(154,26,'Xanh navy · M','PAW-0026-2-2','M','Xanh navy',834000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(155,26,'Đỏ · S','PAW-0026-3-1','S','Đỏ',804000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(156,26,'Đỏ · M','PAW-0026-3-2','M','Đỏ',834000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(157,27,'Kem · S','PAW-0027-1-1','S','Kem',831000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(158,27,'Kem · M','PAW-0027-1-2','M','Kem',861000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(159,27,'Xanh navy · S','PAW-0027-2-1','S','Xanh navy',831000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(160,27,'Xanh navy · M','PAW-0027-2-2','M','Xanh navy',861000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(161,27,'Đỏ · S','PAW-0027-3-1','S','Đỏ',831000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(162,27,'Đỏ · M','PAW-0027-3-2','M','Đỏ',861000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(163,28,'Kem · S','PAW-0028-1-1','S','Kem',858000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(164,28,'Kem · M','PAW-0028-1-2','M','Kem',888000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(165,28,'Xanh navy · S','PAW-0028-2-1','S','Xanh navy',858000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(166,28,'Xanh navy · M','PAW-0028-2-2','M','Xanh navy',888000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(167,28,'Đỏ · S','PAW-0028-3-1','S','Đỏ',858000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(168,28,'Đỏ · M','PAW-0028-3-2','M','Đỏ',888000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(169,29,'Be · Tiêu chuẩn','PAW-0029-1-1','Tiêu chuẩn','Be',885000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(170,29,'Be · Lớn','PAW-0029-1-2','Lớn','Be',915000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(171,29,'Đen · Tiêu chuẩn','PAW-0029-2-1','Tiêu chuẩn','Đen',885000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(172,29,'Đen · Lớn','PAW-0029-2-2','Lớn','Đen',915000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(173,29,'Xanh rêu · Tiêu chuẩn','PAW-0029-3-1','Tiêu chuẩn','Xanh rêu',885000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(174,29,'Xanh rêu · Lớn','PAW-0029-3-2','Lớn','Xanh rêu',915000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(175,30,'Be · Tiêu chuẩn','PAW-0030-1-1','Tiêu chuẩn','Be',912000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(176,30,'Be · Lớn','PAW-0030-1-2','Lớn','Be',942000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(177,30,'Đen · Tiêu chuẩn','PAW-0030-2-1','Tiêu chuẩn','Đen',912000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(178,30,'Đen · Lớn','PAW-0030-2-2','Lớn','Đen',942000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(179,30,'Xanh rêu · Tiêu chuẩn','PAW-0030-3-1','Tiêu chuẩn','Xanh rêu',912000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(180,30,'Xanh rêu · Lớn','PAW-0030-3-2','Lớn','Xanh rêu',942000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(181,31,'Be · Tiêu chuẩn','PAW-0031-1-1','Tiêu chuẩn','Be',939000,3,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(182,31,'Be · Lớn','PAW-0031-1-2','Lớn','Be',969000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(183,31,'Đen · Tiêu chuẩn','PAW-0031-2-1','Tiêu chuẩn','Đen',939000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(184,31,'Đen · Lớn','PAW-0031-2-2','Lớn','Đen',969000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(185,31,'Xanh rêu · Tiêu chuẩn','PAW-0031-3-1','Tiêu chuẩn','Xanh rêu',939000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(186,31,'Xanh rêu · Lớn','PAW-0031-3-2','Lớn','Xanh rêu',969000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(187,32,'Be · Tiêu chuẩn','PAW-0032-1-1','Tiêu chuẩn','Be',966000,4,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(188,32,'Be · Lớn','PAW-0032-1-2','Lớn','Be',996000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(189,32,'Đen · Tiêu chuẩn','PAW-0032-2-1','Tiêu chuẩn','Đen',966000,5,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(190,32,'Đen · Lớn','PAW-0032-2-2','Lớn','Đen',996000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(191,32,'Xanh rêu · Tiêu chuẩn','PAW-0032-3-1','Tiêu chuẩn','Xanh rêu',966000,6,1,'2026-09-26 08:47:32','2026-09-26 08:47:32'),(192,32,'Xanh rêu · Lớn','PAW-0032-3-2','Lớn','Xanh rêu',996000,7,1,'2026-09-26 08:47:32','2026-09-26 08:47:32');
/*!40000 ALTER TABLE `product_variants` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `sku` varchar(255) NOT NULL,
  `pet_type` varchar(255) NOT NULL DEFAULT 'all',
  `short_description` text NOT NULL,
  `description` longtext DEFAULT NULL,
  `detail_heading` varchar(160) DEFAULT NULL,
  `highlights` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`highlights`)),
  `specifications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`specifications`)),
  `care_instructions` longtext DEFAULT NULL,
  `size_guide` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`size_guide`)),
  `content_image` varchar(1000) DEFAULT NULL,
  `price` decimal(12,0) NOT NULL,
  `compare_price` decimal(12,0) DEFAULT NULL,
  `stock` int(10) unsigned NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `rating` decimal(2,1) NOT NULL DEFAULT 0.0,
  `review_count` int(10) unsigned NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  UNIQUE KEY `products_sku_unique` (`sku`),
  KEY `products_category_id_foreign` (`category_id`),
  KEY `products_pet_type_index` (`pet_type`),
  KEY `products_is_featured_index` (`is_featured`),
  KEY `products_status_index` (`status`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,1,'Bóng cao su Pounce','bong-cao-su-pounce','PAW-0001','dog','Bóng cao su đàn hồi vừa phải, dễ lăn và nảy để bé cún hào hứng vận động mỗi ngày.','Pounce được tạo cho những buổi chơi ném bắt ngắn trong nhà hoặc ngoài sân. Bề mặt có rãnh giúp bé dễ ngoạm, còn màu sắc tươi giúp bạn nhanh chóng tìm lại bóng sau mỗi lượt chơi.','Một vòng chạy vui bắt đầu từ cú nảy đầu tiên.','[\"\\u0110\\u1ed9 n\\u1ea3y v\\u1eeba ph\\u1ea3i, t\\u1ea1o chuy\\u1ec3n \\u0111\\u1ed9ng kh\\u00f3 \\u0111o\\u00e1n.\",\"R\\u00e3nh n\\u1ed5i h\\u1ed7 tr\\u1ee3 b\\u00e9 ngo\\u1ea1m v\\u00e0 nh\\u1ea3 b\\u00f3ng.\",\"D\\u1ec5 r\\u1eeda s\\u1ea1ch sau khi ch\\u01a1i ngo\\u00e0i tr\\u1eddi.\",\"Hai k\\u00edch th\\u01b0\\u1edbc cho nhi\\u1ec1u v\\u00f3c d\\u00e1ng ch\\u00f3.\"]','[{\"label\":\"Ch\\u1ea5t li\\u1ec7u\",\"value\":\"Cao su TPR\"},{\"label\":\"K\\u00edch th\\u01b0\\u1edbc\",\"value\":\"Nh\\u1ecf \\u00d8 5 cm \\u00b7 L\\u1edbn \\u00d8 7 cm\"},{\"label\":\"Ph\\u00f9 h\\u1ee3p\",\"value\":\"Ch\\u00f3 ch\\u01a1i n\\u00e9m b\\u1eaft c\\u00f3 gi\\u00e1m s\\u00e1t\"},{\"label\":\"M\\u1ee9c \\u0111\\u1ed9 v\\u1eadn \\u0111\\u1ed9ng\",\"value\":\"V\\u1eeba \\u0111\\u1ebfn cao\"}]','Rửa bằng nước ấm và xà phòng dịu nhẹ, để khô tự nhiên. Kiểm tra bề mặt trước mỗi lần chơi và ngưng sử dụng khi bóng nứt hoặc bong mảnh.','[{\"size\":\"Nh\\u1ecf\",\"guidance\":\"\\u00d8 5 cm \\u00b7 g\\u1ee3i \\u00fd cho b\\u00e9 d\\u01b0\\u1edbi 8 kg\"},{\"size\":\"L\\u1edbn\",\"guidance\":\"\\u00d8 7 cm \\u00b7 g\\u1ee3i \\u00fd cho b\\u00e9 t\\u1eeb 8\\u201320 kg\"}]',NULL,129000,209000,27,'/media/products/pawora-clean/pawora-product-0001.png',5.0,1,1,'active','2026-09-26 08:47:32','2026-09-26 08:47:33',NULL),(2,1,'Dây thừng Tug & Joy','day-thung-tug-joy','PAW-0002','dog','Dây thừng bện chắc tay cho trò kéo co, giúp bạn và bé cún có thêm một khoảng chơi thật gần nhau.','Tug & Joy kết hợp thân dây bện và hai đầu cầm để bạn chủ động nhịp chơi cùng bé. Kết cấu sợi tạo cảm giác thú vị khi ngoạm, phù hợp cho các phiên chơi ngắn có tương tác.','Kéo một đầu, nối gần thêm một chút.','[\"S\\u1ee3i cotton b\\u1ec7n nhi\\u1ec1u l\\u1edbp.\",\"Hai \\u0111\\u1ea7u c\\u1ea7m gi\\u00fap ki\\u1ec3m so\\u00e1t tr\\u00f2 ch\\u01a1i.\",\"K\\u1ebft c\\u1ea5u m\\u1ec1m h\\u01a1n \\u0111\\u1ed3 ch\\u01a1i nh\\u1ef1a c\\u1ee9ng.\",\"D\\u00f9ng cho k\\u00e9o co v\\u00e0 n\\u00e9m t\\u00ecm c\\u00f3 gi\\u00e1m s\\u00e1t.\"]','[{\"label\":\"Ch\\u1ea5t li\\u1ec7u\",\"value\":\"D\\u00e2y cotton pha polyester\"},{\"label\":\"Chi\\u1ec1u d\\u00e0i\",\"value\":\"Nh\\u1ecf 28 cm \\u00b7 L\\u1edbn 42 cm\"},{\"label\":\"Ki\\u1ec3u ch\\u01a1i\",\"value\":\"K\\u00e9o co, n\\u00e9m t\\u00ecm\"},{\"label\":\"Ph\\u00f9 h\\u1ee3p\",\"value\":\"Ch\\u00f3 t\\u1eeb nh\\u1ecf \\u0111\\u1ebfn trung b\\u00ecnh\"}]','Giặt tay riêng bằng nước mát, bóp nhẹ và phơi thật khô. Không để bé nhai một mình; thay sản phẩm khi sợi bung dài hoặc nút thắt lỏng.','[{\"size\":\"Nh\\u1ecf\",\"guidance\":\"D\\u00e0i 28 cm \\u00b7 g\\u1ee3i \\u00fd cho b\\u00e9 d\\u01b0\\u1edbi 10 kg\"},{\"size\":\"L\\u1edbn\",\"guidance\":\"D\\u00e0i 42 cm \\u00b7 g\\u1ee3i \\u00fd cho b\\u00e9 t\\u1eeb 10\\u201325 kg\"}]',NULL,156000,NULL,33,'/media/products/pawora-clean/pawora-product-0002.png',4.0,1,1,'active','2026-09-26 08:47:32','2026-09-26 08:47:33',NULL),(3,1,'Đĩa bay Flexi Disc','dia-bay-flexi-disc','PAW-0003','dog','Đĩa bay mềm dẻo, nhẹ tay khi ném và êm hơn khi bé cún bắt đĩa giữa lúc chạy.','Flexi Disc có vành bo tròn và thân đĩa dẻo để bạn dễ mang theo trong túi đi chơi. Thiết kế nhẹ phù hợp luyện bắt đĩa ở cự ly ngắn trước khi tăng dần khoảng cách.','Cho những buổi chiều cần thêm một đường bay.','[\"V\\u00e0nh m\\u1ec1m, d\\u1ec5 c\\u1ea7m v\\u00e0 ngo\\u1ea1m.\",\"Th\\u00e2n \\u0111\\u0129a nh\\u1eb9, thu\\u1eadn ti\\u1ec7n mang theo.\",\"M\\u00e0u n\\u1ed5i d\\u1ec5 quan s\\u00e1t tr\\u00ean n\\u1ec1n c\\u1ecf.\",\"Ph\\u00f9 h\\u1ee3p t\\u1eadp b\\u1eaft \\u0111\\u0129a t\\u1eebng b\\u01b0\\u1edbc.\"]','[{\"label\":\"Ch\\u1ea5t li\\u1ec7u\",\"value\":\"TPE d\\u1ebbo\"},{\"label\":\"\\u0110\\u01b0\\u1eddng k\\u00ednh\",\"value\":\"Nh\\u1ecf 18 cm \\u00b7 L\\u1edbn 23 cm\"},{\"label\":\"Kh\\u00f4ng gian d\\u00f9ng\",\"value\":\"S\\u00e2n c\\u1ecf ho\\u1eb7c khu v\\u1ef1c tho\\u00e1ng\"},{\"label\":\"Ki\\u1ec3u ch\\u01a1i\",\"value\":\"N\\u00e9m b\\u1eaft c\\u00f3 gi\\u00e1m s\\u00e1t\"}]','Lau hoặc rửa sạch sau khi dùng, tránh phơi lâu dưới nắng gắt. Không dùng như đồ gặm; kiểm tra vành đĩa nếu bé thường cắn mạnh.','[{\"size\":\"Nh\\u1ecf\",\"guidance\":\"\\u00d8 18 cm \\u00b7 cho b\\u00e9 nh\\u1ecf, m\\u1edbi t\\u1eadp b\\u1eaft\"},{\"size\":\"L\\u1edbn\",\"guidance\":\"\\u00d8 23 cm \\u00b7 cho b\\u00e9 trung b\\u00ecnh, kh\\u00f4ng gian r\\u1ed9ng\"}]',NULL,183000,NULL,34,'/media/products/pawora-clean/pawora-product-0003.png',5.0,1,1,'active','2026-09-26 08:47:32','2026-09-26 08:47:33',NULL),(4,1,'Đồ chơi phát tiếng Nibble','do-choi-phat-tieng-nibble','PAW-0004','dog','Đồ chơi phát tiếng nhỏ khi bóp, tạo tín hiệu vui để giữ sự chú ý của bé cún trong giờ chơi.','Nibble tạo âm thanh khi bé tác động vào thân đồ chơi, phù hợp cho trò tìm kiếm và tương tác tại nhà. Dáng thuôn giúp bé dễ ngoạm, đồng thời giúp bạn cầm chắc khi cùng chơi.','Một tiếng “chít” nhỏ, thêm nhiều phút tò mò.','[\"B\\u1ed9 ph\\u00e1t ti\\u1ebfng n\\u1eb1m b\\u00ean trong th\\u00e2n \\u0111\\u1ed3 ch\\u01a1i.\",\"D\\u00e1ng thu\\u00f4n v\\u1eeba mi\\u1ec7ng ngo\\u1ea1m.\",\"B\\u1ec1 m\\u1eb7t d\\u1ec5 lau s\\u1ea1ch.\",\"H\\u1ed7 tr\\u1ee3 tr\\u00f2 t\\u00ecm \\u0111\\u1ed3 v\\u1eadt trong nh\\u00e0.\"]','[{\"label\":\"Ch\\u1ea5t li\\u1ec7u\",\"value\":\"Vinyl m\\u1ec1m\"},{\"label\":\"Chi\\u1ec1u d\\u00e0i\",\"value\":\"Nh\\u1ecf 12 cm \\u00b7 L\\u1edbn 18 cm\"},{\"label\":\"\\u00c2m thanh\",\"value\":\"Ph\\u00e1t ti\\u1ebfng khi b\\u00f3p\"},{\"label\":\"Ph\\u00f9 h\\u1ee3p\",\"value\":\"Ch\\u00f3 ch\\u01a1i t\\u01b0\\u01a1ng t\\u00e1c c\\u00f3 gi\\u00e1m s\\u00e1t\"}]','Lau bằng khăn ẩm rồi để khô. Không ngâm nước lâu để tránh ảnh hưởng bộ phát tiếng; bỏ sản phẩm nếu thân bị thủng hoặc lộ chi tiết bên trong.','[{\"size\":\"Nh\\u1ecf\",\"guidance\":\"D\\u00e0i 12 cm \\u00b7 g\\u1ee3i \\u00fd cho b\\u00e9 d\\u01b0\\u1edbi 8 kg\"},{\"size\":\"L\\u1edbn\",\"guidance\":\"D\\u00e0i 18 cm \\u00b7 g\\u1ee3i \\u00fd cho b\\u00e9 t\\u1eeb 8\\u201318 kg\"}]',NULL,210000,290000,30,'/media/products/pawora-clean/pawora-product-0004.png',4.0,1,1,'active','2026-09-26 08:47:32','2026-09-26 08:47:33',NULL),(5,2,'Cần câu lông vũ Miso','can-cau-long-vu-miso','PAW-0005','cat','Cần câu nhẹ với cụm lông vũ chuyển động linh hoạt, khơi gợi bản năng rình bắt của bé mèo.','Miso giúp bạn tạo các nhịp bay, dừng và đổi hướng như một con mồi nhỏ. Tay cầm dài giữ khoảng cách vừa đủ để bé mèo chạy, vươn và bật nhảy trong những phiên chơi ngắn.','Một chuyển động khẽ cũng đủ mở màn cuộc săn.','[\"C\\u1ee5m l\\u00f4ng v\\u0169 t\\u1ea1o chuy\\u1ec3n \\u0111\\u1ed9ng t\\u1ef1 nhi\\u00ean.\",\"C\\u1ea7n d\\u00e0i gi\\u00fap \\u0111i\\u1ec1u khi\\u1ec3n linh ho\\u1ea1t.\",\"Tay c\\u1ea7m g\\u1ecdn v\\u00e0 nh\\u1eb9.\",\"Khuy\\u1ebfn kh\\u00edch b\\u00e9 v\\u1eadn \\u0111\\u1ed9ng c\\u00f9ng b\\u1ea1n.\"]','[{\"label\":\"Ch\\u1ea5t li\\u1ec7u\",\"value\":\"C\\u1ea7n th\\u00e9p d\\u1ebbo, l\\u00f4ng v\\u0169, polyester\"},{\"label\":\"Chi\\u1ec1u d\\u00e0i\",\"value\":\"Nh\\u1ecf 70 cm \\u00b7 L\\u1edbn 90 cm\"},{\"label\":\"Ki\\u1ec3u ch\\u01a1i\",\"value\":\"R\\u00ecnh, \\u0111u\\u1ed5i v\\u00e0 b\\u1eaft\"},{\"label\":\"Ph\\u00f9 h\\u1ee3p\",\"value\":\"M\\u00e8o ch\\u01a1i c\\u00f3 gi\\u00e1m s\\u00e1t\"}]','Lau tay cầm và dây bằng khăn khô. Cất trong ngăn kín sau khi chơi; không để bé nhai dây hoặc nuốt phần lông rời.','[{\"size\":\"Nh\\u1ecf\",\"guidance\":\"T\\u1ed5ng d\\u00e0i kho\\u1ea3ng 70 cm \\u00b7 ph\\u00f9 h\\u1ee3p kh\\u00f4ng gian nh\\u1ecf\"},{\"size\":\"L\\u1edbn\",\"guidance\":\"T\\u1ed5ng d\\u00e0i kho\\u1ea3ng 90 cm \\u00b7 ph\\u00f9 h\\u1ee3p v\\u00f9ng ch\\u01a1i r\\u1ed9ng\"}]',NULL,237000,NULL,26,'/media/products/pawora-clean/pawora-product-0005.png',4.5,2,1,'active','2026-09-26 08:47:32','2026-09-26 08:47:33',NULL),(6,2,'Bóng len Moon Ball','bong-len-moon-ball','PAW-0006','cat','Bóng len nhẹ lăn êm trên sàn, vừa đủ để bé mèo vờn bằng chân và tự khám phá quanh nhà.','Moon Ball được quấn sợi thành khối tròn nhẹ, tạo chuyển động chậm và ít tiếng động hơn bóng nhựa. Đây là lựa chọn hợp với những góc chơi trong nhà và các bé mèo thích vờn đồ vật nhỏ.','Lăn thật khẽ cho một buổi chơi không ồn ào.','[\"Tr\\u1ecdng l\\u01b0\\u1ee3ng nh\\u1eb9, d\\u1ec5 l\\u0103n b\\u1eb1ng ch\\u00e2n.\",\"Chuy\\u1ec3n \\u0111\\u1ed9ng \\u00eam tr\\u00ean s\\u00e0n c\\u1ee9ng.\",\"B\\u1ec1 m\\u1eb7t s\\u1ee3i t\\u1ea1o c\\u1ea3m gi\\u00e1c t\\u00f2 m\\u00f2.\",\"Hai c\\u1ee1 cho m\\u00e8o con v\\u00e0 m\\u00e8o tr\\u01b0\\u1edfng th\\u00e0nh.\"]','[{\"label\":\"Ch\\u1ea5t li\\u1ec7u\",\"value\":\"S\\u1ee3i polyester qu\\u1ea5n l\\u00f5i nh\\u1eb9\"},{\"label\":\"\\u0110\\u01b0\\u1eddng k\\u00ednh\",\"value\":\"Nh\\u1ecf 4 cm \\u00b7 L\\u1edbn 6 cm\"},{\"label\":\"\\u00c2m thanh\",\"value\":\"Kh\\u00f4ng g\\u1eafn chu\\u00f4ng\"},{\"label\":\"Kh\\u00f4ng gian d\\u00f9ng\",\"value\":\"Trong nh\\u00e0\"}]','Dùng khăn ẩm chấm sạch, để khô hoàn toàn trước khi chơi lại. Cắt bỏ sợi tưa ngắn và thay bóng nếu lớp quấn bị bung.','[{\"size\":\"Nh\\u1ecf\",\"guidance\":\"\\u00d8 4 cm \\u00b7 g\\u1ee3i \\u00fd cho m\\u00e8o con tr\\u00ean 4 th\\u00e1ng\"},{\"size\":\"L\\u1edbn\",\"guidance\":\"\\u00d8 6 cm \\u00b7 g\\u1ee3i \\u00fd cho m\\u00e8o tr\\u01b0\\u1edfng th\\u00e0nh\"}]',NULL,264000,NULL,27,'/media/products/pawora-clean/pawora-product-0006.png',4.0,2,1,'active','2026-09-26 08:47:32','2026-09-26 08:47:33',NULL),(7,2,'Bàn cào móng Cloud','ban-cao-mong-cloud','PAW-0007','cat','Bàn cào dáng lượn sóng cho bé mèo duỗi người, cào móng và nằm nghỉ ở cùng một góc quen.','Cloud tạo bề mặt cào nằm ngang với độ cong ôm theo tư thế vươn của mèo. Kết cấu carton nhiều lớp cho cảm giác bám móng rõ ràng và có thể đặt gần sofa hoặc chỗ ngủ bé thường lui tới.','Một đường cong vừa vặn cho cả lúc cào lẫn lúc nằm.','[\"M\\u1eb7t cong h\\u1ed7 tr\\u1ee3 t\\u01b0 th\\u1ebf du\\u1ed7i d\\u00e0i.\",\"Carton \\u00e9p l\\u1edbp cho m\\u00f3ng b\\u00e1m r\\u00f5.\",\"C\\u00f3 th\\u1ec3 d\\u00f9ng nh\\u01b0 m\\u1ed9t ch\\u1ed7 n\\u1eb1m ng\\u1eafn.\",\"Gi\\u00fap \\u0111\\u1ecbnh h\\u01b0\\u1edbng khu v\\u1ef1c c\\u00e0o trong nh\\u00e0.\"]','[{\"label\":\"Ch\\u1ea5t li\\u1ec7u\",\"value\":\"Gi\\u1ea5y carton \\u00e9p l\\u1edbp\"},{\"label\":\"K\\u00edch th\\u01b0\\u1edbc\",\"value\":\"Nh\\u1ecf 42 \\u00d7 20 \\u00d7 8 cm \\u00b7 L\\u1edbn 55 \\u00d7 25 \\u00d7 10 cm\"},{\"label\":\"Ki\\u1ec3u c\\u00e0o\",\"value\":\"N\\u1eb1m ngang\"},{\"label\":\"Ph\\u00f9 h\\u1ee3p\",\"value\":\"M\\u00e8o d\\u00f9ng trong nh\\u00e0\"}]','Đặt ở nơi khô ráo, hút vụn giấy định kỳ và tránh làm ướt. Đảo chiều mặt cào khi một vùng đã mòn; thay mới khi kết cấu mất độ chắc.','[{\"size\":\"Nh\\u1ecf\",\"guidance\":\"D\\u00e0i 42 cm \\u00b7 cho m\\u00e8o d\\u01b0\\u1edbi 4 kg\"},{\"size\":\"L\\u1edbn\",\"guidance\":\"D\\u00e0i 55 cm \\u00b7 cho m\\u00e8o t\\u1eeb 4\\u20137 kg\"}]',NULL,291000,371000,33,'/media/products/pawora-clean/pawora-product-0007.png',4.0,2,1,'active','2026-09-26 08:47:32','2026-09-26 08:47:33',NULL),(8,2,'Đường hầm Hide & Seek','duong-ham-hide-seek','PAW-0008','cat','Đường hầm vải gấp gọn, tạo lối ẩn nấp và chạy xuyên qua cho những bé mèo mê khám phá.','Hide & Seek biến một góc nhà thành nơi rình, nấp và bất ngờ lao ra của bé mèo. Khung lò xo giữ đường hầm mở khi chơi, sau đó có thể thu gọn để cất khi cần thêm không gian.','Một lối đi bí mật ngay giữa phòng khách.','[\"Khung l\\u00f2 xo t\\u1ef1 gi\\u1eef phom.\",\"V\\u1ea3i ph\\u00e1t ti\\u1ebfng s\\u1ed9t so\\u1ea1t nh\\u1eb9 khi ch\\u1ea1m.\",\"C\\u1eeda s\\u1ed5 b\\u00ean h\\u00f4ng t\\u0103ng c\\u00e1ch ch\\u01a1i.\",\"G\\u1ea5p g\\u1ecdn b\\u1eb1ng d\\u00e2y gi\\u1eef \\u0111i k\\u00e8m.\"]','[{\"label\":\"Ch\\u1ea5t li\\u1ec7u\",\"value\":\"Polyester, khung th\\u00e9p \\u0111\\u00e0n h\\u1ed3i\"},{\"label\":\"K\\u00edch th\\u01b0\\u1edbc\",\"value\":\"Nh\\u1ecf \\u00d8 24 \\u00d7 70 cm \\u00b7 L\\u1edbn \\u00d8 28 \\u00d7 100 cm\"},{\"label\":\"C\\u1ea5u tr\\u00fac\",\"value\":\"M\\u1ed9t \\u1ed1ng, m\\u1ed9t c\\u1eeda s\\u1ed5 b\\u00ean\"},{\"label\":\"Kh\\u00f4ng gian d\\u00f9ng\",\"value\":\"Trong nh\\u00e0\"}]','Hút lông hoặc lau bằng khăn ẩm, phơi nơi thoáng mát. Kiểm tra lớp bọc quanh khung; ngưng dùng nếu dây thép lộ ra ngoài.','[{\"size\":\"Nh\\u1ecf\",\"guidance\":\"\\u00d8 24 cm, d\\u00e0i 70 cm \\u00b7 cho m\\u00e8o d\\u01b0\\u1edbi 5 kg\"},{\"size\":\"L\\u1edbn\",\"guidance\":\"\\u00d8 28 cm, d\\u00e0i 100 cm \\u00b7 cho m\\u00e8o t\\u1eeb 5\\u20138 kg ho\\u1eb7c nh\\u00e0 nhi\\u1ec1u m\\u00e8o\"}]',NULL,318000,NULL,34,'/media/products/pawora-clean/pawora-product-0008.png',3.5,2,1,'active','2026-09-26 08:47:32','2026-09-26 08:47:33',NULL),(9,3,'Vòng cổ AirTag Linen','vong-co-airtag-linen','PAW-0009','all','Vòng cổ vải linen mềm, có ngăn giữ thiết bị định vị để bạn yên tâm hơn trong những chuyến đi cùng bé.','AirTag Linen giấu gọn ngăn định vị vào mặt ngoài vòng cổ để thiết bị ít vướng khi bé vận động. Lớp lót mềm và khóa điều chỉnh giúp bạn căn độ ôm vừa phải cho sinh hoạt hằng ngày.','Gọn trên cổ, thêm một lớp an tâm khi ra ngoài.','[\"Ng\\u0103n gi\\u1eef thi\\u1ebft b\\u1ecb n\\u1eb1m s\\u00e1t th\\u00e2n v\\u00f2ng.\",\"L\\u1edbp l\\u00f3t m\\u1ec1m \\u1edf m\\u1eb7t ti\\u1ebfp x\\u00fac.\",\"Kh\\u00f3a t\\u0103ng gi\\u1ea3m d\\u1ec5 c\\u0103n ch\\u1ec9nh.\",\"Khoen ri\\u00eang \\u0111\\u1ec3 g\\u1eafn th\\u1ebb t\\u00ean.\"]','[{\"label\":\"Ch\\u1ea5t li\\u1ec7u\",\"value\":\"V\\u1ea3i linen pha, l\\u00f3t polyester\"},{\"label\":\"B\\u1ea3n d\\u00e2y\",\"value\":\"S 1,5 cm \\u00b7 M 2 cm\"},{\"label\":\"Thi\\u1ebft b\\u1ecb t\\u01b0\\u01a1ng th\\u00edch\",\"value\":\"AirTag 31,9 mm (kh\\u00f4ng k\\u00e8m thi\\u1ebft b\\u1ecb)\"},{\"label\":\"Ph\\u00f9 h\\u1ee3p\",\"value\":\"Ch\\u00f3 v\\u00e0 m\\u00e8o \\u0111eo c\\u00f3 gi\\u00e1m s\\u00e1t\"}]','Tháo thiết bị định vị trước khi giặt tay. Phơi khô tự nhiên, kiểm tra khóa và đường may định kỳ; luôn chừa khoảng hai ngón tay giữa vòng và cổ bé.','[{\"size\":\"S\",\"guidance\":\"V\\u00f2ng c\\u1ed5 22\\u201332 cm \\u00b7 b\\u1ea3n 1,5 cm\"},{\"size\":\"M\",\"guidance\":\"V\\u00f2ng c\\u1ed5 30\\u201345 cm \\u00b7 b\\u1ea3n 2 cm\"}]',NULL,345000,NULL,30,'/media/products/pawora-clean/pawora-product-0009.jpg',4.0,2,0,'active','2026-09-26 08:47:32','2026-09-26 08:47:33',NULL),(10,3,'Dây dắt Urban Walk','day-dat-urban-walk','PAW-0010','all','Dây dắt nhẹ và chắc tay cho những vòng đi bộ hằng ngày, từ ngõ nhỏ đến công viên quen.','Urban Walk cân bằng giữa tay cầm êm và thân dây gọn để bạn dễ điều khiển trong các lộ trình thường ngày. Móc xoay giúp hạn chế dây tự xoắn khi bé đổi hướng liên tục.','Giữ nhịp bước đều giữa phố đông.','[\"M\\u00f3c kh\\u00f3a xoay 360\\u00b0.\",\"Tay c\\u1ea7m l\\u00f3t m\\u1ec1m, d\\u1ec5 n\\u1eafm.\",\"\\u0110\\u01b0\\u1eddng may gia c\\u1ed1 \\u1edf \\u0111i\\u1ec3m ch\\u1ecbu l\\u1ef1c.\",\"Khoen ph\\u1ee5 \\u0111\\u1ec3 treo t\\u00fai \\u0111\\u1ef1ng r\\u00e1c.\"]','[{\"label\":\"Ch\\u1ea5t li\\u1ec7u\",\"value\":\"D\\u00e2y polyester d\\u1ec7t, m\\u00f3c h\\u1ee3p kim k\\u1ebdm\"},{\"label\":\"K\\u00edch th\\u01b0\\u1edbc\",\"value\":\"S 1,5 \\u00d7 150 cm \\u00b7 M 2 \\u00d7 150 cm\"},{\"label\":\"Ki\\u1ec3u d\\u00e2y\",\"value\":\"Chi\\u1ec1u d\\u00e0i c\\u1ed1 \\u0111\\u1ecbnh\"},{\"label\":\"Ph\\u00f9 h\\u1ee3p\",\"value\":\"\\u0110i b\\u1ed9 h\\u1eb1ng ng\\u00e0y\"}]','Lau sạch sau khi đi mưa hoặc giặt tay bằng nước mát. Để móc khô hoàn toàn và kiểm tra đường may trước mỗi chuyến đi.','[{\"size\":\"S\",\"guidance\":\"B\\u1ea3n 1,5 cm \\u00b7 g\\u1ee3i \\u00fd cho b\\u00e9 d\\u01b0\\u1edbi 10 kg\"},{\"size\":\"M\",\"guidance\":\"B\\u1ea3n 2 cm \\u00b7 g\\u1ee3i \\u00fd cho b\\u00e9 t\\u1eeb 10\\u201320 kg\"}]',NULL,372000,452000,26,'/media/products/pawora-clean/pawora-product-0010.jpg',4.0,2,0,'active','2026-09-26 08:47:32','2026-09-26 08:47:33',NULL),(11,3,'Yếm ngực Comfort Pro','yem-nguc-comfort-pro','PAW-0011','all','Yếm ngực đệm êm, phân bổ lực quanh thân để bé thoải mái hơn khi tập đi dây.','Comfort Pro dùng kết cấu chữ Y để chừa khoảng vận động cho vai trước của bé. Hai điểm tăng giảm giúp yếm nằm ổn định mà không cần siết quá chặt quanh cổ.','Ôm vừa phần ngực, để mỗi bước đi tự nhiên hơn.','[\"D\\u00e1ng ch\\u1eef Y h\\u1ed7 tr\\u1ee3 vai chuy\\u1ec3n \\u0111\\u1ed9ng.\",\"L\\u1edbp l\\u01b0\\u1edbi tho\\u00e1ng \\u1edf m\\u1eb7t trong.\",\"Hai \\u0111i\\u1ec3m ch\\u1ec9nh v\\u00f2ng c\\u1ed5 v\\u00e0 v\\u00f2ng ng\\u1ef1c.\",\"D\\u1ea3i ph\\u1ea3n quang h\\u1ed7 tr\\u1ee3 quan s\\u00e1t khi thi\\u1ebfu s\\u00e1ng.\"]','[{\"label\":\"Ch\\u1ea5t li\\u1ec7u\",\"value\":\"Polyester, l\\u01b0\\u1edbi spacer, kh\\u00f3a POM\"},{\"label\":\"\\u0110i\\u1ec3m g\\u1eafn d\\u00e2y\",\"value\":\"Khoen l\\u01b0ng\"},{\"label\":\"\\u0110i\\u1ec3m \\u0111i\\u1ec1u ch\\u1ec9nh\",\"value\":\"V\\u00f2ng c\\u1ed5 v\\u00e0 v\\u00f2ng ng\\u1ef1c\"},{\"label\":\"Ph\\u00f9 h\\u1ee3p\",\"value\":\"Ch\\u00f3 \\u0111i b\\u1ed9 h\\u1eb1ng ng\\u00e0y\"}]','Cài khóa trước khi giặt tay, dùng nước mát và phơi nơi thoáng. Khi mặc, chừa khoảng hai ngón tay và kiểm tra vùng nách không bị cọ.','[{\"size\":\"S\",\"guidance\":\"V\\u00f2ng ng\\u1ef1c 36\\u201348 cm \\u00b7 v\\u00f2ng c\\u1ed5 28\\u201338 cm\"},{\"size\":\"M\",\"guidance\":\"V\\u00f2ng ng\\u1ef1c 46\\u201362 cm \\u00b7 v\\u00f2ng c\\u1ed5 36\\u201348 cm\"}]',NULL,399000,NULL,27,'/media/products/pawora-clean/pawora-product-0011.jpg',5.0,1,0,'active','2026-09-26 08:47:32','2026-09-26 08:47:33',NULL),(12,3,'Bộ dây dắt Weekend','bo-day-dat-weekend','PAW-0012','all','Bộ yếm và dây dắt đồng màu, sẵn sàng cho những chuyến đi cuối tuần cùng bé.','Weekend kết hợp yếm ôm thân và dây dắt đồng bộ để bạn chuẩn bị nhanh trước khi ra cửa. Quai chỉnh rõ ràng giúp căn vừa vòng ngực, còn tay cầm có đệm tạo cảm giác dễ chịu trong chuyến đi dài hơn.','Mọi thứ cần cho cuộc dạo chơi, gói trong một bộ.','[\"Tr\\u1ecdn b\\u1ed9 y\\u1ebfm ng\\u1ef1c v\\u00e0 d\\u00e2y d\\u1eaft.\",\"Quai ch\\u1ec9nh hai b\\u00ean th\\u00e2n.\",\"Tay c\\u1ea7m d\\u00e2y d\\u1eaft c\\u00f3 l\\u1edbp \\u0111\\u1ec7m.\",\"M\\u00e0u s\\u1eafc \\u0111\\u1ed3ng b\\u1ed9, d\\u1ec5 ph\\u1ed1i ph\\u1ee5 ki\\u1ec7n.\"]','[{\"label\":\"Ch\\u1ea5t li\\u1ec7u\",\"value\":\"Polyester d\\u1ec7t, kh\\u00f3a POM, m\\u00f3c h\\u1ee3p kim\"},{\"label\":\"Chi\\u1ec1u d\\u00e0i d\\u00e2y\",\"value\":\"150 cm\"},{\"label\":\"Th\\u00e0nh ph\\u1ea7n\",\"value\":\"1 y\\u1ebfm ng\\u1ef1c, 1 d\\u00e2y d\\u1eaft\"},{\"label\":\"Ph\\u00f9 h\\u1ee3p\",\"value\":\"Ch\\u00f3 \\u0111i d\\u1ea1o v\\u00e0 du l\\u1ecbch\"}]','Giặt tay riêng, đóng khóa trước khi giặt và phơi khô hoàn toàn. Kiểm tra móc, khóa và độ vừa của yếm trước mỗi lần sử dụng.','[{\"size\":\"S\",\"guidance\":\"V\\u00f2ng ng\\u1ef1c 38\\u201350 cm \\u00b7 g\\u1ee3i \\u00fd 4\\u20139 kg\"},{\"size\":\"M\",\"guidance\":\"V\\u00f2ng ng\\u1ef1c 48\\u201366 cm \\u00b7 g\\u1ee3i \\u00fd 9\\u201318 kg\"}]',NULL,426000,NULL,33,'/media/products/pawora-clean/pawora-product-0012.jpg',5.0,1,0,'active','2026-09-26 08:47:32','2026-09-26 08:47:33',NULL),(13,4,'Giường ngủ Cocoon','giuong-ngu-cocoon','PAW-0013','all','Giường thành cao ôm quanh cơ thể, tạo một góc nghỉ êm và riêng cho chó mèo trong nhà.','Cocoon có viền nâng nhẹ để bé tựa đầu hoặc cuộn mình khi nghỉ. Lõi đệm rời giúp bạn chỉnh phom và làm sạch thuận tiện hơn trong nhịp chăm sóc hằng tuần.','Một chiếc kén mềm cho giấc ngủ sâu hơn.','[\"Vi\\u1ec1n cao t\\u1ea1o c\\u1ea3m gi\\u00e1c \\u00f4m quanh.\",\"\\u0110\\u1ec7m gi\\u1eefa c\\u00f3 th\\u1ec3 th\\u00e1o r\\u1eddi.\",\"\\u0110\\u00e1y ph\\u1ee7 h\\u1ea1t ch\\u1ed1ng tr\\u01b0\\u1ee3t.\",\"T\\u00f4ng m\\u00e0u d\\u1ecbu, d\\u1ec5 \\u0111\\u1eb7t trong nhi\\u1ec1u kh\\u00f4ng gian.\"]','[{\"label\":\"Ch\\u1ea5t li\\u1ec7u\",\"value\":\"V\\u1ea3i polyester, b\\u00f4ng s\\u1ee3i r\\u1ed7ng\"},{\"label\":\"K\\u00edch th\\u01b0\\u1edbc\",\"value\":\"S \\u00d8 50 \\u00d7 18 cm \\u00b7 M \\u00d8 65 \\u00d7 20 cm\"},{\"label\":\"C\\u1ea5u tr\\u00fac\",\"value\":\"Vi\\u1ec1n li\\u1ec1n, \\u0111\\u1ec7m gi\\u1eefa th\\u00e1o r\\u1eddi\"},{\"label\":\"Ph\\u00f9 h\\u1ee3p\",\"value\":\"Ch\\u00f3 v\\u00e0 m\\u00e8o d\\u00f9ng trong nh\\u00e0\"}]','Hút lông thường xuyên. Giặt riêng đệm giữa bằng chế độ nhẹ hoặc giặt tay, dàn đều bông khi còn ẩm và phơi khô hoàn toàn trước khi dùng.','[{\"size\":\"S\",\"guidance\":\"\\u00d8 50 cm \\u00b7 cho b\\u00e9 cu\\u1ed9n m\\u00ecnh d\\u01b0\\u1edbi 6 kg\"},{\"size\":\"M\",\"guidance\":\"\\u00d8 65 cm \\u00b7 cho b\\u00e9 cu\\u1ed9n m\\u00ecnh t\\u1eeb 6\\u201312 kg\"}]',NULL,453000,533000,34,'/media/products/pawora-clean/pawora-product-0013.jpg',4.0,1,0,'active','2026-09-26 08:47:32','2026-09-26 08:47:33',NULL),(14,4,'Nệm mây Soft Nest','nem-may-soft-nest','PAW-0014','all','Nệm phẳng êm, thoáng và dễ bố trí trong chuồng, góc phòng hoặc bên cạnh sofa.','Soft Nest chọn phom chữ nhật tối giản để bé dễ bước lên, xoay người và duỗi chân. Vỏ nệm tháo rời giúp việc vệ sinh gọn hơn, phù hợp dùng như chỗ nghỉ chính hoặc lớp lót bổ sung.','Một mặt nằm êm, vừa đủ cho mọi góc quen.','[\"M\\u1eb7t n\\u1ec7m ph\\u1eb3ng, d\\u1ec5 l\\u00ean xu\\u1ed1ng.\",\"V\\u1ecf ngo\\u00e0i c\\u00f3 kh\\u00f3a k\\u00e9o th\\u00e1o r\\u1eddi.\",\"\\u0110\\u00e1y ch\\u1ed1ng tr\\u01b0\\u1ee3t nh\\u1eb9.\",\"Phom ch\\u1eef nh\\u1eadt d\\u1ec5 \\u0111\\u1eb7t trong chu\\u1ed3ng.\"]','[{\"label\":\"Ch\\u1ea5t li\\u1ec7u\",\"value\":\"V\\u1ea3i d\\u1ec7t polyester, l\\u00f5i b\\u00f4ng \\u00e9p\"},{\"label\":\"K\\u00edch th\\u01b0\\u1edbc\",\"value\":\"S 55 \\u00d7 40 \\u00d7 8 cm \\u00b7 M 75 \\u00d7 55 \\u00d7 10 cm\"},{\"label\":\"V\\u1ecf n\\u1ec7m\",\"value\":\"Th\\u00e1o r\\u1eddi b\\u1eb1ng kh\\u00f3a k\\u00e9o\"},{\"label\":\"Ph\\u00f9 h\\u1ee3p\",\"value\":\"Ch\\u00f3 v\\u00e0 m\\u00e8o d\\u00f9ng trong nh\\u00e0\"}]','Tháo vỏ và giặt bằng nước mát; lau lõi bằng khăn ẩm, không ngâm. Hong khô hoàn toàn cả vỏ và lõi trước khi lắp lại.','[{\"size\":\"S\",\"guidance\":\"55 \\u00d7 40 cm \\u00b7 cho b\\u00e9 n\\u1eb1m d\\u00e0i \\u0111\\u1ebfn kho\\u1ea3ng 45 cm\"},{\"size\":\"M\",\"guidance\":\"75 \\u00d7 55 cm \\u00b7 cho b\\u00e9 n\\u1eb1m d\\u00e0i \\u0111\\u1ebfn kho\\u1ea3ng 65 cm\"}]',NULL,480000,NULL,30,'/media/products/pawora-clean/pawora-product-0014.jpg',2.0,1,0,'active','2026-09-26 08:47:32','2026-09-26 08:47:33',NULL),(15,4,'Chăn cotton Calm','chan-cotton-calm','PAW-0015','all','Chăn cotton mềm nhẹ để lót ổ, phủ nệm hoặc mang theo giúp bé nhận ra mùi quen khi đi xa.','Calm là lớp chăn mỏng vừa đủ cho điều hòa, đêm se lạnh hoặc những chuyến đi. Bề mặt cotton chải nhẹ tạo cảm giác mềm, còn kích thước gọn giúp bạn dễ gấp trong túi đồ của bé.','Mang theo một mảng ấm quen thuộc của nhà.','[\"Cotton ch\\u1ea3i m\\u1ec1m \\u1edf b\\u1ec1 m\\u1eb7t.\",\"\\u0110\\u1ed9 d\\u00e0y nh\\u1eb9, d\\u1ec5 g\\u1ea5p g\\u1ecdn.\",\"D\\u00f9ng \\u0111\\u1ec3 ph\\u1ee7 n\\u1ec7m ho\\u1eb7c l\\u00f3t \\u1ed5.\",\"Hai c\\u1ee1 cho g\\u00f3c ngh\\u1ec9 v\\u00e0 l\\u1ed3ng v\\u1eadn chuy\\u1ec3n.\"]','[{\"label\":\"Ch\\u1ea5t li\\u1ec7u\",\"value\":\"Cotton pha polyester\"},{\"label\":\"K\\u00edch th\\u01b0\\u1edbc\",\"value\":\"S 70 \\u00d7 50 cm \\u00b7 M 100 \\u00d7 75 cm\"},{\"label\":\"\\u0110\\u1ed9 d\\u00e0y\",\"value\":\"Ch\\u0103n m\\u1ecfng b\\u1ed1n m\\u00f9a\"},{\"label\":\"Ph\\u00f9 h\\u1ee3p\",\"value\":\"Ch\\u00f3 v\\u00e0 m\\u00e8o\"}]','Giặt máy chế độ nhẹ với màu tương đồng, không dùng chất tẩy mạnh. Kéo phẳng khi phơi và bảo quản nơi khô thoáng.','[{\"size\":\"S\",\"guidance\":\"70 \\u00d7 50 cm \\u00b7 l\\u00f3t \\u1ed5 ho\\u1eb7c l\\u1ed3ng nh\\u1ecf\"},{\"size\":\"M\",\"guidance\":\"100 \\u00d7 75 cm \\u00b7 ph\\u1ee7 n\\u1ec7m ho\\u1eb7c l\\u1ed3ng v\\u1eeba\"}]',NULL,507000,NULL,26,'/media/products/pawora-clean/pawora-product-0015.jpg',5.0,1,0,'active','2026-09-26 08:47:32','2026-09-26 08:47:33',NULL),(16,4,'Nhà vải Hideaway','nha-vai-hideaway','PAW-0016','all','Nhà vải kín đáo với đệm rời, dành cho những bé thích thu mình trong một góc riêng yên tĩnh.','Hideaway tạo không gian trú ẩn có mái che để chó mèo nghỉ khi muốn tách khỏi chuyển động xung quanh. Khung foam giữ phom nhẹ và có thể ép gọn khi cần cất hoặc di chuyển.','Một khoảng riêng nhỏ giữa căn nhà thân thuộc.','[\"M\\u00e1i che t\\u1ea1o c\\u1ea3m gi\\u00e1c k\\u00edn \\u0111\\u00e1o.\",\"\\u0110\\u1ec7m \\u0111\\u00e1y th\\u00e1o r\\u1eddi hai m\\u1eb7t.\",\"Khung foam nh\\u1eb9, d\\u1ec5 di chuy\\u1ec3n.\",\"C\\u1eeda v\\u00e0o bo th\\u1ea5p \\u0111\\u1ec3 b\\u00e9 b\\u01b0\\u1edbc qua.\"]','[{\"label\":\"Ch\\u1ea5t li\\u1ec7u\",\"value\":\"V\\u1ea3i polyester, foam \\u0111\\u1ecbnh h\\u00ecnh\"},{\"label\":\"K\\u00edch th\\u01b0\\u1edbc\",\"value\":\"S 38 \\u00d7 35 \\u00d7 35 cm \\u00b7 M 48 \\u00d7 43 \\u00d7 42 cm\"},{\"label\":\"\\u0110\\u1ec7m \\u0111\\u00e1y\",\"value\":\"Th\\u00e1o r\\u1eddi\"},{\"label\":\"Kh\\u00f4ng gian d\\u00f9ng\",\"value\":\"Trong nh\\u00e0\"}]','Hút lông bên trong, giặt tay đệm rời và lau thân nhà bằng khăn ẩm. Nắn lại phom khi còn hơi ẩm rồi để khô tự nhiên.','[{\"size\":\"S\",\"guidance\":\"\\u0110\\u00e1y 38 \\u00d7 35 cm \\u00b7 cho b\\u00e9 d\\u01b0\\u1edbi 5 kg\"},{\"size\":\"M\",\"guidance\":\"\\u0110\\u00e1y 48 \\u00d7 43 cm \\u00b7 cho b\\u00e9 t\\u1eeb 5\\u20139 kg\"}]',NULL,534000,614000,27,'/media/products/pawora-clean/pawora-product-0016.jpg',0.0,0,0,'active','2026-09-26 08:47:32','2026-09-26 08:47:33',NULL),(17,5,'Bát sứ Slow Dining','bat-su-slow-dining','PAW-0017','all','Bát sứ có gờ chia nhịp ăn, giúp bữa ăn của bé diễn ra chậm và đều hơn.','Slow Dining sắp xếp các gờ thấp bên trong lòng bát để chia thức ăn thành nhiều khoảng nhỏ. Chân silicone giữ bát ổn định hơn trên sàn, phù hợp với hạt khô hoặc thức ăn ướt dạng đặc.','Chậm lại một chút để bữa ăn dễ chịu hơn.','[\"G\\u1edd th\\u1ea5p chia nh\\u1ecf l\\u01b0\\u1ee3ng th\\u1ee9c \\u0103n.\",\"Men s\\u1ee9 nh\\u1eb5n, d\\u1ec5 quan s\\u00e1t \\u0111\\u1ed9 s\\u1ea1ch.\",\"V\\u00f2ng silicone h\\u1ea1n ch\\u1ebf tr\\u01b0\\u1ee3t.\",\"D\\u00f9ng \\u0111\\u01b0\\u1ee3c v\\u1edbi h\\u1ea1t kh\\u00f4 v\\u00e0 pate \\u0111\\u1eb7c.\"]','[{\"label\":\"Ch\\u1ea5t li\\u1ec7u\",\"value\":\"S\\u1ee9 tr\\u00e1ng men, v\\u00f2ng silicone\"},{\"label\":\"Dung t\\u00edch\",\"value\":\"350 ml \\u00b7 550 ml\"},{\"label\":\"K\\u00edch th\\u01b0\\u1edbc h\\u1ea1t\",\"value\":\"G\\u1edd cao kho\\u1ea3ng 2 cm\"},{\"label\":\"Ph\\u00f9 h\\u1ee3p\",\"value\":\"Ch\\u00f3 v\\u00e0 m\\u00e8o \\u0103n nhanh\"}]','Rửa sau mỗi bữa bằng miếng rửa mềm, tháo vòng silicone để làm sạch riêng và để khô. Không dùng bát khi có vết nứt hoặc mẻ men.','[{\"size\":\"350 ml\",\"guidance\":\"Kh\\u1ea9u ph\\u1ea7n nh\\u1ecf \\u00b7 g\\u1ee3i \\u00fd cho b\\u00e9 d\\u01b0\\u1edbi 6 kg\"},{\"size\":\"550 ml\",\"guidance\":\"Kh\\u1ea9u ph\\u1ea7n v\\u1eeba \\u00b7 g\\u1ee3i \\u00fd cho b\\u00e9 t\\u1eeb 6\\u201315 kg\"}]',NULL,561000,NULL,33,'/media/products/pawora-clean/pawora-product-0017.jpg',0.0,0,0,'active','2026-09-26 08:47:32','2026-09-26 08:47:33',NULL),(18,5,'Bình nước Travel Sip','binh-nuoc-travel-sip','PAW-0018','all','Bình nước gắn chuồng với đầu uống có van, giữ khu vực nghỉ của bé gọn và khô hơn.','Travel Sip cố định vào nan chuồng bằng ngàm vặn và cấp nước qua đầu bi khi bé liếm. Thân bình trong giúp bạn theo dõi mực nước, phù hợp khi ở nhà hoặc trong hành trình ngắn.','Nước uống luôn ở đúng chỗ, kể cả khi bé di chuyển.','[\"Ng\\u00e0m v\\u1eb7n \\u0111i\\u1ec1u ch\\u1ec9nh theo nan chu\\u1ed3ng.\",\"\\u0110\\u1ea7u bi c\\u1ea5p n\\u01b0\\u1edbc khi b\\u00e9 li\\u1ebfm.\",\"Th\\u00e2n trong d\\u1ec5 theo d\\u00f5i m\\u1ef1c n\\u01b0\\u1edbc.\",\"C\\u00f3 th\\u1ec3 th\\u00e1o b\\u00ecnh \\u0111\\u1ec3 ch\\u00e2m n\\u01b0\\u1edbc nhanh.\"]','[{\"label\":\"Ch\\u1ea5t li\\u1ec7u\",\"value\":\"Nh\\u1ef1a PET, \\u0111\\u1ea7u u\\u1ed1ng th\\u00e9p kh\\u00f4ng g\\u1ec9\"},{\"label\":\"Dung t\\u00edch\",\"value\":\"350 ml \\u00b7 550 ml\"},{\"label\":\"Ki\\u1ec3u l\\u1eafp\",\"value\":\"G\\u1eafn nan chu\\u1ed3ng b\\u1eb1ng ng\\u00e0m v\\u1eb7n\"},{\"label\":\"Ph\\u00f9 h\\u1ee3p\",\"value\":\"Ch\\u00f3, m\\u00e8o \\u0111\\u00e3 quen \\u0111\\u1ea7u u\\u1ed1ng bi\"}]','Rửa bình hằng ngày, vệ sinh đầu bi bằng cọ nhỏ và kiểm tra nước chảy trước khi để bé sử dụng. Không dùng nước nóng hoặc máy rửa chén.','[{\"size\":\"350 ml\",\"guidance\":\"Cho l\\u1ed3ng nh\\u1ecf ho\\u1eb7c h\\u00e0nh tr\\u00ecnh ng\\u1eafn\"},{\"size\":\"550 ml\",\"guidance\":\"Cho l\\u1ed3ng v\\u1eeba ho\\u1eb7c th\\u1eddi gian s\\u1eed d\\u1ee5ng d\\u00e0i h\\u01a1n\"}]',NULL,588000,NULL,34,'/media/products/pawora-clean/pawora-product-0018.jpg',0.0,0,0,'active','2026-09-26 08:47:32','2026-09-26 08:47:33',NULL),(19,5,'Bát đôi Oak Stand','bat-doi-oak-stand','PAW-0019','all','Bộ hai bát đặt trên chân gỗ thấp, gom gọn thức ăn và nước uống vào một góc đẹp mắt.','Oak Stand nâng hai bát lên khỏi mặt sàn và giữ chúng trong cùng một khung chắc chắn. Bát thép tháo rời giúp bạn châm thức ăn, thay nước và vệ sinh nhanh sau mỗi bữa.','Một trạm ăn gọn gàng cho nhịp sinh hoạt mỗi ngày.','[\"Hai b\\u00e1t ri\\u00eang cho th\\u1ee9c \\u0103n v\\u00e0 n\\u01b0\\u1edbc.\",\"B\\u00e1t th\\u00e9p th\\u00e1o r\\u1eddi d\\u1ec5 r\\u1eeda.\",\"Ch\\u00e2n \\u0111\\u1ebf c\\u00f3 mi\\u1ebfng ch\\u1ed1ng tr\\u01b0\\u1ee3t.\",\"Khung g\\u1ed7 t\\u1ea1o g\\u00f3c \\u0103n g\\u1ecdn m\\u1eaft.\"]','[{\"label\":\"Ch\\u1ea5t li\\u1ec7u\",\"value\":\"G\\u1ed7 cao su ph\\u1ee7 d\\u1ea7u, b\\u00e1t th\\u00e9p kh\\u00f4ng g\\u1ec9\"},{\"label\":\"Dung t\\u00edch m\\u1ed7i b\\u00e1t\",\"value\":\"350 ml \\u00b7 550 ml\"},{\"label\":\"S\\u1ed1 b\\u00e1t\",\"value\":\"2 b\\u00e1t th\\u00e1o r\\u1eddi\"},{\"label\":\"Ph\\u00f9 h\\u1ee3p\",\"value\":\"Ch\\u00f3 v\\u00e0 m\\u00e8o\"}]','Rửa bát sau mỗi bữa và lau khô trước khi đặt lại. Lau khung bằng khăn ẩm rồi khăn khô; không ngâm khung gỗ trong nước.','[{\"size\":\"350 ml\",\"guidance\":\"Khung 30 \\u00d7 15 \\u00d7 8 cm \\u00b7 cho b\\u00e9 nh\\u1ecf\"},{\"size\":\"550 ml\",\"guidance\":\"Khung 38 \\u00d7 19 \\u00d7 10 cm \\u00b7 cho b\\u00e9 v\\u1eeba\"}]',NULL,615000,695000,30,'/media/products/pawora-clean/pawora-product-0019.jpg',0.0,0,0,'active','2026-09-26 08:47:32','2026-09-26 08:47:33',NULL),(20,5,'Máy nước Fresh Flow','may-nuoc-fresh-flow','PAW-0020','all','Máy nước tuần hoàn tạo dòng chảy nhẹ, khuyến khích bé ghé uống thường xuyên hơn trong ngày.','Fresh Flow luân chuyển nước qua lõi lọc và đưa trở lại khay bằng vòi chảy êm. Cửa sổ mực nước giúp bạn biết khi nào cần châm thêm, còn các bộ phận chính có thể tháo rời để vệ sinh.','Một dòng nước nhỏ giữ góc uống luôn sinh động.','[\"D\\u00f2ng n\\u01b0\\u1edbc tu\\u1ea7n ho\\u00e0n li\\u00ean t\\u1ee5c.\",\"B\\u1ed9 l\\u1ecdc r\\u1eddi h\\u1ed7 tr\\u1ee3 gi\\u1eef c\\u1eb7n th\\u00f4.\",\"C\\u1eeda s\\u1ed5 quan s\\u00e1t m\\u1ef1c n\\u01b0\\u1edbc.\",\"B\\u01a1m \\u0111i\\u1ec7n \\u00e1p th\\u1ea5p v\\u1eadn h\\u00e0nh \\u00eam.\"]','[{\"label\":\"Ch\\u1ea5t li\\u1ec7u\",\"value\":\"Nh\\u1ef1a PP, b\\u01a1m \\u0111i\\u1ec7n \\u00e1p th\\u1ea5p\"},{\"label\":\"Dung t\\u00edch\",\"value\":\"1,3 l\\u00edt \\u00b7 2 l\\u00edt\"},{\"label\":\"Ngu\\u1ed3n \\u0111i\\u1ec7n\",\"value\":\"USB 5V\"},{\"label\":\"B\\u1ed9 s\\u1ea3n ph\\u1ea9m\",\"value\":\"M\\u00e1y, b\\u01a1m, 1 l\\u00f5i l\\u1ecdc, c\\u00e1p USB\"}]','Châm nước không thấp hơn vạch MIN. Rửa khay và vệ sinh bơm mỗi tuần; thay lõi lọc sau khoảng 2–4 tuần tùy nguồn nước và số lượng thú cưng. Ngắt điện trước khi vệ sinh.','[{\"size\":\"1,3 l\\u00edt\",\"guidance\":\"G\\u1ee3i \\u00fd cho 1 b\\u00e9 nh\\u1ecf\"},{\"size\":\"2 l\\u00edt\",\"guidance\":\"G\\u1ee3i \\u00fd cho b\\u00e9 l\\u1edbn ho\\u1eb7c nh\\u00e0 c\\u00f3 2 b\\u00e9\"}]',NULL,642000,NULL,26,'/media/products/pawora-clean/pawora-product-0020.jpg',0.0,0,0,'active','2026-09-26 08:47:32','2026-09-26 08:47:33',NULL),(21,6,'Lược chải Easy Coat','luoc-chai-easy-coat','PAW-0021','all','Lược chải đầu kim bọc tròn, gom lông rụng nhẹ nhàng trong thói quen chăm lông tại nhà.','Easy Coat có mặt lược cong nhẹ để đi theo đường cơ thể của bé. Đầu kim bọc tròn và tay cầm chống trượt giúp bạn kiểm soát lực khi chải từng vùng nhỏ.','Một nhịp chải đều cho bộ lông gọn và thoáng.','[\"\\u0110\\u1ea7u kim b\\u1ecdc tr\\u00f2n gi\\u1ea3m c\\u1ea3m gi\\u00e1c s\\u1eafc.\",\"M\\u1eb7t l\\u01b0\\u1ee3c cong theo \\u0111\\u01b0\\u1eddng c\\u01a1 th\\u1ec3.\",\"N\\u00fat \\u0111\\u1ea9y h\\u1ed7 tr\\u1ee3 l\\u1ea5y l\\u00f4ng kh\\u1ecfi m\\u1eb7t l\\u01b0\\u1ee3c.\",\"Tay c\\u1ea7m ph\\u1ee7 cao su d\\u1ec5 ki\\u1ec3m so\\u00e1t.\"]','[{\"label\":\"Ch\\u1ea5t li\\u1ec7u\",\"value\":\"Kim th\\u00e9p kh\\u00f4ng g\\u1ec9, nh\\u1ef1a ABS, TPR\"},{\"label\":\"M\\u1eb7t l\\u01b0\\u1ee3c\",\"value\":\"Ti\\u00eau chu\\u1ea9n 8 \\u00d7 5 cm \\u00b7 L\\u1edbn 11 \\u00d7 7 cm\"},{\"label\":\"Lo\\u1ea1i l\\u00f4ng\",\"value\":\"L\\u00f4ng ng\\u1eafn \\u0111\\u1ebfn l\\u00f4ng d\\u00e0i\"},{\"label\":\"Ph\\u00f9 h\\u1ee3p\",\"value\":\"Ch\\u00f3 v\\u00e0 m\\u00e8o\"}]','Chải theo chiều lông, bắt đầu bằng lực nhẹ và tránh vùng da đang kích ứng. Lấy lông khỏi lược sau mỗi lần dùng, lau khô và bảo quản nơi thoáng.','[{\"size\":\"Ti\\u00eau chu\\u1ea9n\",\"guidance\":\"M\\u1eb7t 8 \\u00d7 5 cm \\u00b7 cho b\\u00e9 nh\\u1ecf v\\u00e0 v\\u00f9ng kh\\u00f3 ch\\u1ea3i\"},{\"size\":\"L\\u1edbn\",\"guidance\":\"M\\u1eb7t 11 \\u00d7 7 cm \\u00b7 cho b\\u00e9 v\\u1eeba ho\\u1eb7c b\\u1ed9 l\\u00f4ng d\\u00e0y\"}]',NULL,669000,NULL,27,'/media/products/pawora-clean/pawora-product-0021.jpg',0.0,0,0,'active','2026-09-26 08:47:32','2026-09-26 08:47:33',NULL),(22,6,'Kềm cắt móng Safe Trim','kem-cat-mong-safe-trim','PAW-0022','all','Kềm cắt móng có chốt giới hạn và tay cầm bám chắc, giúp thao tác chăm móng tại nhà rõ ràng hơn.','Safe Trim kết hợp lưỡi cong với chốt giới hạn phía sau để bạn dễ căn phần móng cần cắt. Khóa đóng giữ lưỡi gọn khi cất, còn dũa đi kèm giúp làm mượt cạnh móng sau cùng.','Gọn từng chút một, bình tĩnh trong mỗi lần cắt.','[\"L\\u01b0\\u1ee1i cong theo d\\u00e1ng m\\u00f3ng.\",\"Ch\\u1ed1t gi\\u1edbi h\\u1ea1n h\\u1ed7 tr\\u1ee3 c\\u0103n \\u0111\\u1ed9 s\\u00e2u.\",\"Kh\\u00f3a \\u0111\\u00f3ng an to\\u00e0n khi c\\u1ea5t gi\\u1eef.\",\"K\\u00e8m d\\u0169a nh\\u1ecf \\u0111\\u1ec3 l\\u00e0m m\\u01b0\\u1ee3t c\\u1ea1nh m\\u00f3ng.\"]','[{\"label\":\"Ch\\u1ea5t li\\u1ec7u\",\"value\":\"L\\u01b0\\u1ee1i th\\u00e9p kh\\u00f4ng g\\u1ec9, tay c\\u1ea7m TPR\"},{\"label\":\"K\\u00edch th\\u01b0\\u1edbc\",\"value\":\"Ti\\u00eau chu\\u1ea9n 12 cm \\u00b7 L\\u1edbn 16 cm\"},{\"label\":\"Ph\\u1ee5 ki\\u1ec7n\",\"value\":\"1 d\\u0169a m\\u00f3ng\"},{\"label\":\"Ph\\u00f9 h\\u1ee3p\",\"value\":\"Ch\\u00f3 v\\u00e0 m\\u00e8o\"}]','Chỉ cắt phần đầu móng trong, tránh vùng tủy móng; nếu chưa quen, nên nhờ người có kinh nghiệm hướng dẫn. Lau lưỡi sau khi dùng, khóa kềm và cất xa trẻ em.','[{\"size\":\"Ti\\u00eau chu\\u1ea9n\",\"guidance\":\"D\\u00e0i 12 cm \\u00b7 cho m\\u00e8o v\\u00e0 ch\\u00f3 nh\\u1ecf\"},{\"size\":\"L\\u1edbn\",\"guidance\":\"D\\u00e0i 16 cm \\u00b7 cho ch\\u00f3 v\\u1eeba, m\\u00f3ng d\\u00e0y h\\u01a1n\"}]',NULL,696000,776000,33,'/media/products/pawora-clean/pawora-product-0022.jpg',0.0,0,0,'active','2026-09-26 08:47:32','2026-09-26 08:47:33',NULL),(23,6,'Găng tay Gentle Groom','gang-tay-gentle-groom','PAW-0023','all','Găng tay silicone mềm giúp gom lông rụng trong lúc vuốt ve hoặc tắm cho bé.','Gentle Groom đưa các núm silicone mềm lên lòng bàn tay để việc chải gần với một lần vuốt ve. Quai cổ tay điều chỉnh giúp găng nằm chắc khi dùng trên lông khô hoặc trong lúc tắm.','Chăm lông bằng chính chuyển động bé đã quen.','[\"N\\u00fam silicone m\\u1ec1m tr\\u00ean l\\u00f2ng b\\u00e0n tay.\",\"D\\u00f9ng \\u0111\\u01b0\\u1ee3c khi ch\\u1ea3i kh\\u00f4 ho\\u1eb7c t\\u1eafm.\",\"Quai d\\u00e1n \\u0111i\\u1ec1u ch\\u1ec9nh \\u1edf c\\u1ed5 tay.\",\"L\\u1edbp l\\u00f4ng gom l\\u1ea1i d\\u1ec5 b\\u00f3c kh\\u1ecfi g\\u0103ng.\"]','[{\"label\":\"Ch\\u1ea5t li\\u1ec7u\",\"value\":\"Silicone, l\\u01b0\\u1edbi polyester\"},{\"label\":\"K\\u00edch th\\u01b0\\u1edbc\",\"value\":\"Ti\\u00eau chu\\u1ea9n 23 \\u00d7 17 cm \\u00b7 L\\u1edbn 25 \\u00d7 19 cm\"},{\"label\":\"Tay \\u0111eo\",\"value\":\"D\\u00f9ng cho tay ph\\u1ea3i\"},{\"label\":\"Ph\\u00f9 h\\u1ee3p\",\"value\":\"Ch\\u00f3 v\\u00e0 m\\u00e8o\"}]','Bắt đầu bằng lượt vuốt ngắn để bé làm quen, không chà mạnh vùng da nhạy cảm. Rửa găng sau khi dùng và treo khô hoàn toàn.','[{\"size\":\"Ti\\u00eau chu\\u1ea9n\",\"guidance\":\"Ngang l\\u00f2ng b\\u00e0n tay \\u0111\\u1ebfn 9 cm\"},{\"size\":\"L\\u1edbn\",\"guidance\":\"Ngang l\\u00f2ng b\\u00e0n tay 9\\u201311 cm\"}]',NULL,723000,NULL,34,'/media/products/pawora-clean/pawora-product-0023.jpg',0.0,0,0,'active','2026-09-26 08:47:32','2026-09-26 08:47:33',NULL),(24,6,'Khăn tắm Quick Dry','khan-tam-quick-dry','PAW-0024','all','Khăn sợi nhỏ thấm nhanh, giúp rút ngắn khoảng thời gian bộ lông của bé còn ướt sau khi tắm.','Quick Dry dùng bề mặt microfiber mềm với độ dày vừa đủ để thấm nước bằng thao tác bóp nhẹ. Dáng khăn dài dễ quấn quanh thân chó mèo và gọn hơn khăn tắm gia đình khi mang đi.','Ôm trọn lớp nước trước khi bé kịp chạy quanh nhà.','[\"S\\u1ee3i microfiber th\\u1ea5m n\\u01b0\\u1edbc nhanh.\",\"B\\u1ec1 m\\u1eb7t m\\u1ec1m cho thao t\\u00e1c b\\u00f3p kh\\u00f4.\",\"Kh\\u0103n nh\\u1eb9, g\\u1ea5p g\\u1ecdn khi di chuy\\u1ec3n.\",\"Hai c\\u1ee1 theo chi\\u1ec1u d\\u00e0i c\\u01a1 th\\u1ec3 b\\u00e9.\"]','[{\"label\":\"Ch\\u1ea5t li\\u1ec7u\",\"value\":\"Microfiber polyester\\/polyamide\"},{\"label\":\"K\\u00edch th\\u01b0\\u1edbc\",\"value\":\"Ti\\u00eau chu\\u1ea9n 70 \\u00d7 35 cm \\u00b7 L\\u1edbn 100 \\u00d7 50 cm\"},{\"label\":\"Ki\\u1ec3u d\\u1ec7t\",\"value\":\"S\\u1ee3i nh\\u1ecf hai m\\u1eb7t\"},{\"label\":\"Ph\\u00f9 h\\u1ee3p\",\"value\":\"Ch\\u00f3 v\\u00e0 m\\u00e8o\"}]','Giặt trước lần dùng đầu tiên và giặt riêng với nước mát. Không dùng nước xả vải vì có thể giảm độ thấm; phơi khô sau mỗi lần sử dụng.','[{\"size\":\"Ti\\u00eau chu\\u1ea9n\",\"guidance\":\"70 \\u00d7 35 cm \\u00b7 cho m\\u00e8o v\\u00e0 ch\\u00f3 d\\u01b0\\u1edbi 10 kg\"},{\"size\":\"L\\u1edbn\",\"guidance\":\"100 \\u00d7 50 cm \\u00b7 cho ch\\u00f3 t\\u1eeb 10\\u201325 kg ho\\u1eb7c l\\u00f4ng d\\u00e0i\"}]',NULL,750000,NULL,30,'/media/products/pawora-clean/pawora-product-0024.jpg',0.0,0,0,'active','2026-09-26 08:47:32','2026-09-26 08:47:33',NULL),(25,7,'Áo thun Everyday','ao-thun-everyday','PAW-0025','all','Áo thun cotton co giãn nhẹ, tạo một lớp mặc gọn gàng cho ngày thường và những buổi chụp ảnh cùng bé.','Everyday giữ phom vừa người với phần bụng cắt cao để bé di chuyển và đi vệ sinh thuận tiện. Cổ và nách áo dùng bo mềm, phù hợp mặc trong thời gian ngắn khi thời tiết dễ chịu.','Một chiếc áo đơn giản cho vẻ ngoài thật tự nhiên.','[\"Cotton co gi\\u00e3n nh\\u1eb9 theo chuy\\u1ec3n \\u0111\\u1ed9ng.\",\"Ph\\u1ea7n b\\u1ee5ng c\\u1eaft cao, d\\u1ec5 sinh ho\\u1ea1t.\",\"Bo c\\u1ed5 v\\u00e0 n\\u00e1ch m\\u1ec1m.\",\"Phom c\\u01a1 b\\u1ea3n, d\\u1ec5 ph\\u1ed1i ph\\u1ee5 ki\\u1ec7n.\"]','[{\"label\":\"Ch\\u1ea5t li\\u1ec7u\",\"value\":\"Cotton 95%, spandex 5%\"},{\"label\":\"\\u0110\\u1ed9 co gi\\u00e3n\",\"value\":\"Co gi\\u00e3n hai chi\\u1ec1u\"},{\"label\":\"Ki\\u1ec3u m\\u1eb7c\",\"value\":\"Chui \\u0111\\u1ea7u\"},{\"label\":\"Ph\\u00f9 h\\u1ee3p\",\"value\":\"Ch\\u00f3 v\\u00e0 m\\u00e8o \\u0111\\u00e3 quen m\\u1eb7c \\u00e1o\"}]','Giặt mặt trái bằng nước mát, không dùng chất tẩy mạnh và phơi ngang nơi thoáng. Tháo áo nếu bé thở gấp, cào áo liên tục hoặc vận động khó khăn.','[{\"size\":\"S\",\"guidance\":\"Ng\\u1ef1c 34\\u201340 cm \\u00b7 l\\u01b0ng 24 cm \\u00b7 c\\u1ed5 24\\u201328 cm\"},{\"size\":\"M\",\"guidance\":\"Ng\\u1ef1c 40\\u201348 cm \\u00b7 l\\u01b0ng 30 cm \\u00b7 c\\u1ed5 28\\u201333 cm\"}]',NULL,777000,857000,26,'/media/products/pawora-clean/pawora-product-0025.jpg',0.0,0,0,'active','2026-09-26 08:47:32','2026-09-26 08:47:33',NULL),(26,7,'Áo mưa City Walk','ao-mua-city-walk','PAW-0026','all','Áo mưa nhẹ có mũ gập và dải phản quang, giúp bé gọn hơn trong những vòng đi bộ trời ẩm.','City Walk dùng lớp vải cản nước nhẹ với phần bụng điều chỉnh bằng dải dán, thuận tiện mặc nhanh trước khi ra ngoài. Khe luồn dây ở lưng giúp kết nối với yếm mặc bên trong mà không làm áo xô lệch nhiều.','Giữ phần thân khô hơn khi cơn mưa ghé ngang.','[\"V\\u1ea3i c\\u1ea3n n\\u01b0\\u1edbc cho m\\u01b0a nh\\u1eb9.\",\"M\\u0169 c\\u00f3 th\\u1ec3 g\\u1eadp v\\u1ec1 sau.\",\"Khe lu\\u1ed3n d\\u00e2y d\\u1eaft \\u1edf l\\u01b0ng.\",\"D\\u1ea3i ph\\u1ea3n quang h\\u1ed7 tr\\u1ee3 quan s\\u00e1t.\"]','[{\"label\":\"Ch\\u1ea5t li\\u1ec7u\",\"value\":\"Polyester ph\\u1ee7 PU\"},{\"label\":\"Ki\\u1ec3u \\u0111\\u00f3ng\",\"value\":\"D\\u1ea3i d\\u00e1n d\\u01b0\\u1edbi b\\u1ee5ng\"},{\"label\":\"M\\u1ee9c b\\u1ea3o v\\u1ec7\",\"value\":\"M\\u01b0a nh\\u1eb9, chuy\\u1ebfn \\u0111i ng\\u1eafn\"},{\"label\":\"Ph\\u00f9 h\\u1ee3p\",\"value\":\"Ch\\u00f3\"}]','Lau bùn đất ngay sau khi dùng, giặt tay khi cần và phơi mặt trái ở nơi thoáng. Không sấy nóng hoặc ủi trực tiếp lên lớp phủ.','[{\"size\":\"S\",\"guidance\":\"Ng\\u1ef1c 38\\u201346 cm \\u00b7 l\\u01b0ng 28 cm \\u00b7 c\\u1ed5 28\\u201334 cm\"},{\"size\":\"M\",\"guidance\":\"Ng\\u1ef1c 46\\u201356 cm \\u00b7 l\\u01b0ng 36 cm \\u00b7 c\\u1ed5 34\\u201342 cm\"}]',NULL,804000,NULL,27,'/media/products/pawora-clean/pawora-product-0026.jpg',0.0,0,0,'active','2026-09-26 08:47:32','2026-09-26 08:47:33',NULL),(27,7,'Khăn choàng Pawora','khan-choang-pawora','PAW-0027','all','Khăn tam giác mềm nhẹ, tạo điểm nhấn gọn gàng cho những buổi dạo chơi, chụp ảnh hay ngày thường.','Khăn choàng Pawora được cắt theo dáng tam giác để nằm gọn trước ngực và dễ phối cùng nhiều màu lông. Hai nút bấm cho phép điều chỉnh vòng cổ, giúp bạn mặc và tháo nhanh mà không cần buộc nút.','Một điểm nhấn nhỏ cho diện mạo thật riêng.','[\"D\\u00e1ng tam gi\\u00e1c g\\u1ecdn g\\u00e0ng, d\\u1ec5 ph\\u1ed1i.\",\"Hai n\\u1ea5c b\\u1ea5m \\u0111i\\u1ec1u ch\\u1ec9nh v\\u00f2ng c\\u1ed5.\",\"V\\u1ea3i cotton m\\u1ec1m, nh\\u1eb9 tr\\u00ean c\\u1ed5.\",\"Ph\\u00f9 h\\u1ee3p \\u0111i ch\\u01a1i, ch\\u1ee5p \\u1ea3nh v\\u00e0 d\\u00f9ng h\\u1eb1ng ng\\u00e0y.\"]','[{\"label\":\"Ch\\u1ea5t li\\u1ec7u\",\"value\":\"Cotton pha linen\"},{\"label\":\"Ki\\u1ec3u \\u0111\\u00f3ng\",\"value\":\"Hai n\\u1ea5c n\\u00fat b\\u1ea5m\"},{\"label\":\"M\\u1eb7t v\\u1ea3i\",\"value\":\"Hai m\\u1eb7t c\\u00f9ng t\\u00f4ng m\\u00e0u\"},{\"label\":\"Ph\\u00f9 h\\u1ee3p\",\"value\":\"Ch\\u00f3 v\\u00e0 m\\u00e8o \\u0111\\u00e3 quen \\u0111eo ph\\u1ee5 ki\\u1ec7n\"}]','Cài nút trước khi giặt tay hoặc cho vào túi giặt, dùng nước mát và phơi phẳng. Khi đeo, chừa khoảng hai ngón tay giữa khăn và cổ bé.','[{\"size\":\"S\",\"guidance\":\"V\\u00f2ng c\\u1ed5 22\\u201330 cm \\u00b7 d\\u00e0i kh\\u0103n 15 cm\"},{\"size\":\"M\",\"guidance\":\"V\\u00f2ng c\\u1ed5 30\\u201342 cm \\u00b7 d\\u00e0i kh\\u0103n 20 cm\"}]',NULL,831000,NULL,33,'/media/products/pawora-clean/pawora-product-0027.jpg',0.0,0,0,'active','2026-09-26 08:47:32','2026-09-26 08:47:33',NULL),(28,7,'Áo len Warm Hug','ao-len-warm-hug','PAW-0028','all','Áo len dệt mềm với phần bụng gọn, thêm một lớp ấm vừa phải cho bé trong ngày se lạnh.','Warm Hug dùng sợi dệt co giãn và cổ bo thấp để ôm nhẹ quanh thân mà không che khuất chuyển động. Phần bụng được rút ngắn giúp bé sinh hoạt thuận tiện khi mặc trong nhà hoặc dạo chơi ngắn.','Ấm vừa đủ để bé vẫn thoải mái trong từng bước.','[\"S\\u1ee3i d\\u1ec7t m\\u1ec1m, co gi\\u00e3n nh\\u1eb9.\",\"C\\u1ed5 bo th\\u1ea5p, d\\u1ec5 m\\u1eb7c qua \\u0111\\u1ea7u.\",\"Ph\\u1ea7n b\\u1ee5ng c\\u1eaft g\\u1ecdn.\",\"\\u0110\\u1ed9 \\u1ea5m v\\u1eeba cho th\\u1eddi ti\\u1ebft se l\\u1ea1nh.\"]','[{\"label\":\"Ch\\u1ea5t li\\u1ec7u\",\"value\":\"Acrylic pha cotton\"},{\"label\":\"Ki\\u1ec3u d\\u1ec7t\",\"value\":\"D\\u1ec7t g\\u00e2n co gi\\u00e3n\"},{\"label\":\"Ki\\u1ec3u m\\u1eb7c\",\"value\":\"Chui \\u0111\\u1ea7u\"},{\"label\":\"Ph\\u00f9 h\\u1ee3p\",\"value\":\"Ch\\u00f3 v\\u00e0 m\\u00e8o \\u0111\\u00e3 quen m\\u1eb7c \\u00e1o\"}]','Giặt tay bằng nước mát, bóp nhẹ trong khăn khô và phơi ngang để giữ phom. Không treo khi áo còn ướt; tháo áo nếu bé có dấu hiệu quá nóng.','[{\"size\":\"S\",\"guidance\":\"Ng\\u1ef1c 34\\u201340 cm \\u00b7 l\\u01b0ng 25 cm \\u00b7 c\\u1ed5 24\\u201328 cm\"},{\"size\":\"M\",\"guidance\":\"Ng\\u1ef1c 40\\u201348 cm \\u00b7 l\\u01b0ng 32 cm \\u00b7 c\\u1ed5 28\\u201334 cm\"}]',NULL,858000,938000,34,'/media/products/pawora-clean/pawora-product-0028.jpg',0.0,0,0,'active','2026-09-26 08:47:32','2026-09-26 08:47:33',NULL),(29,8,'Túi vận chuyển Go Together','tui-van-chuyen-go-together','PAW-0029','all','Túi vận chuyển thoáng khí với đáy cứng tháo rời, cho những hành trình ngắn cùng chó mèo nhỏ.','Go Together có ba mặt lưới để không khí lưu thông và giúp bạn dễ quan sát bé trong lúc di chuyển. Dây an toàn ngắn bên trong nối với yếm, còn tấm đáy cứng giữ bề mặt nằm ổn định hơn.','Một khoang đi cùng đủ thoáng, gọn và gần bạn.','[\"Ba m\\u1eb7t l\\u01b0\\u1edbi th\\u00f4ng tho\\u00e1ng.\",\"T\\u1ea5m \\u0111\\u00e1y c\\u1ee9ng c\\u00f3 l\\u1edbp b\\u1ecdc th\\u00e1o r\\u1eddi.\",\"D\\u00e2y an to\\u00e0n b\\u00ean trong \\u0111\\u1ec3 n\\u1ed1i v\\u1edbi y\\u1ebfm.\",\"Quai x\\u00e1ch v\\u00e0 d\\u00e2y \\u0111eo vai c\\u00f3 \\u0111\\u1ec7m.\"]','[{\"label\":\"Ch\\u1ea5t li\\u1ec7u\",\"value\":\"Polyester, l\\u01b0\\u1edbi nylon, t\\u1ea5m PE\"},{\"label\":\"K\\u00edch th\\u01b0\\u1edbc\",\"value\":\"Ti\\u00eau chu\\u1ea9n 40 \\u00d7 25 \\u00d7 28 cm \\u00b7 L\\u1edbn 48 \\u00d7 30 \\u00d7 32 cm\"},{\"label\":\"C\\u1eeda m\\u1edf\",\"value\":\"M\\u1eb7t tr\\u00ean v\\u00e0 m\\u1eb7t b\\u00ean\"},{\"label\":\"Ph\\u00f9 h\\u1ee3p\",\"value\":\"Ch\\u00f3 v\\u00e0 m\\u00e8o nh\\u1ecf\"}]','Luôn nối dây an toàn vào yếm, không nối trực tiếp vào vòng cổ và không để bé trong túi không giám sát. Tháo tấm đáy, lau thân túi và phơi khô sau mỗi chuyến đi.','[{\"size\":\"Ti\\u00eau chu\\u1ea9n\",\"guidance\":\"40 \\u00d7 25 \\u00d7 28 cm \\u00b7 b\\u00e9 t\\u1ed1i \\u0111a kho\\u1ea3ng 5 kg\"},{\"size\":\"L\\u1edbn\",\"guidance\":\"48 \\u00d7 30 \\u00d7 32 cm \\u00b7 b\\u00e9 t\\u1ed1i \\u0111a kho\\u1ea3ng 8 kg\"}]',NULL,885000,NULL,30,'/media/products/pawora-clean/pawora-product-0029.jpg',0.0,0,0,'active','2026-09-26 08:47:32','2026-09-26 08:47:33',NULL),(30,8,'Đệm xe Road Trip','dem-xe-road-trip','PAW-0030','all','Đệm xe có thành bao quanh và dây neo ghế, tạo vị trí ngồi riêng cho bé trong những chuyến đi bằng ô tô.','Road Trip đặt trên ghế xe như một chiếc đệm thành cao, giúp gom lông và tạo ranh giới nghỉ cho bé. Dây neo vòng qua tựa ghế giữ đệm ổn định, còn dây ngắn bên trong dùng để nối với yếm.','Một chỗ ngồi quen để hành trình bớt xáo trộn.','[\"Th\\u00e0nh \\u0111\\u1ec7m bao quanh ba ph\\u00eda.\",\"D\\u00e2y neo \\u0111i\\u1ec1u ch\\u1ec9nh theo t\\u1ef1a gh\\u1ebf.\",\"D\\u00e2y gi\\u1eef b\\u00ean trong n\\u1ed1i v\\u1edbi y\\u1ebfm.\",\"V\\u1ecf \\u0111\\u1ec7m th\\u00e1o r\\u1eddi \\u0111\\u1ec3 v\\u1ec7 sinh.\"]','[{\"label\":\"Ch\\u1ea5t li\\u1ec7u\",\"value\":\"Polyester, b\\u00f4ng s\\u1ee3i, \\u0111\\u00e1y ch\\u1ed1ng tr\\u01b0\\u1ee3t\"},{\"label\":\"K\\u00edch th\\u01b0\\u1edbc\",\"value\":\"Ti\\u00eau chu\\u1ea9n 45 \\u00d7 40 cm \\u00b7 L\\u1edbn 60 \\u00d7 50 cm\"},{\"label\":\"V\\u1ecb tr\\u00ed l\\u1eafp\",\"value\":\"Gh\\u1ebf sau \\u00f4 t\\u00f4 c\\u00f3 t\\u1ef1a \\u0111\\u1ea7u\"},{\"label\":\"Ph\\u00f9 h\\u1ee3p\",\"value\":\"Ch\\u00f3 \\u0111i xe c\\u00f9ng ng\\u01b0\\u1eddi gi\\u00e1m s\\u00e1t\"}]','Sản phẩm không thay thế lồng vận chuyển hoặc hệ thống bảo hộ va chạm chuyên dụng. Luôn nối dây giữ vào yếm, kiểm tra điểm neo trước khi xe chạy; tháo vỏ và giặt chế độ nhẹ khi cần.','[{\"size\":\"Ti\\u00eau chu\\u1ea9n\",\"guidance\":\"M\\u1eb7t n\\u1eb1m 45 \\u00d7 40 cm \\u00b7 cho b\\u00e9 d\\u01b0\\u1edbi 8 kg\"},{\"size\":\"L\\u1edbn\",\"guidance\":\"M\\u1eb7t n\\u1eb1m 60 \\u00d7 50 cm \\u00b7 cho b\\u00e9 t\\u1eeb 8\\u201315 kg\"}]',NULL,912000,NULL,26,'/media/products/pawora-clean/pawora-product-0030.jpg',0.0,0,0,'active','2026-09-26 08:47:32','2026-09-26 08:47:33',NULL),(31,8,'Túi đựng snack Walkie','tui-dung-snack-walkie','PAW-0031','all','Túi snack mở nhanh bằng một tay, giữ phần thưởng luôn trong tầm với khi đi dạo hoặc luyện tập cùng bé.','Walkie có miệng túi bán cứng để bạn lấy snack nhanh rồi tự khép lại bằng dây rút. Ngăn phụ giữ túi rác hoặc chìa khóa, trong khi kẹp lưng và đai đeo cho phép đổi cách mang theo thói quen.','Phần thưởng đúng lúc, nhịp học liền mạch hơn.','[\"Mi\\u1ec7ng t\\u00fai r\\u1ed9ng, thao t\\u00e1c b\\u1eb1ng m\\u1ed9t tay.\",\"L\\u1edbp l\\u00f3t ch\\u1ed1ng th\\u1ea5m nh\\u1eb9 d\\u1ec5 lau.\",\"Ng\\u0103n ph\\u1ee5 v\\u00e0 khe r\\u00fat t\\u00fai r\\u00e1c.\",\"C\\u00f3 k\\u1eb9p l\\u01b0ng v\\u00e0 \\u0111ai \\u0111eo \\u0111i\\u1ec1u ch\\u1ec9nh.\"]','[{\"label\":\"Ch\\u1ea5t li\\u1ec7u\",\"value\":\"Polyester ph\\u1ee7 PU, l\\u00f3t PEVA\"},{\"label\":\"K\\u00edch th\\u01b0\\u1edbc\",\"value\":\"Ti\\u00eau chu\\u1ea9n 12 \\u00d7 6 \\u00d7 15 cm \\u00b7 L\\u1edbn 15 \\u00d7 8 \\u00d7 18 cm\"},{\"label\":\"Ki\\u1ec3u \\u0111\\u00f3ng\",\"value\":\"D\\u00e2y r\\u00fat\"},{\"label\":\"C\\u00e1ch \\u0111eo\",\"value\":\"K\\u1eb9p l\\u01b0ng ho\\u1eb7c \\u0111ai h\\u00f4ng\"}]','Đổ vụn thức ăn sau mỗi lần dùng, lau lớp lót bằng khăn ẩm và để mở cho khô. Không cất thức ăn ướt lâu trong túi.','[{\"size\":\"Ti\\u00eau chu\\u1ea9n\",\"guidance\":\"Kho\\u1ea3ng 0,7 l\\u00edt \\u00b7 cho bu\\u1ed5i \\u0111i d\\u1ea1o ng\\u1eafn\"},{\"size\":\"L\\u1edbn\",\"guidance\":\"Kho\\u1ea3ng 1,2 l\\u00edt \\u00b7 cho bu\\u1ed5i t\\u1eadp d\\u00e0i ho\\u1eb7c mang th\\u00eam v\\u1eadt d\\u1ee5ng\"}]',NULL,939000,1019000,27,'/media/products/pawora-clean/pawora-product-0031.jpg',0.0,0,0,'active','2026-09-26 08:47:32','2026-09-26 08:47:33',NULL),(32,8,'Bình nước Pocket Sip','binh-nuoc-pocket-sip','PAW-0032','all','Bình nước cầm tay có máng uống liền thân, cho bé uống nhanh mà không cần mang theo bát riêng.','Pocket Sip mở nước vào máng bằng nút bấm và cho phép hút phần nước còn sạch trở lại bình. Khóa gạt hạn chế bấm nhầm trong túi, còn dây đeo cổ tay giúp bạn mang theo gọn hơn khi dạo bộ.','Một tay châm nước, một nhịp dừng vừa đủ trên đường.','[\"M\\u00e1ng u\\u1ed1ng li\\u1ec1n th\\u00e2n b\\u00ecnh.\",\"N\\u00fat c\\u1ea5p v\\u00e0 thu h\\u1ed3i n\\u01b0\\u1edbc b\\u1eb1ng m\\u1ed9t tay.\",\"Kh\\u00f3a g\\u1ea1t h\\u1ea1n ch\\u1ebf r\\u00f2 khi mang theo.\",\"D\\u00e2y \\u0111eo c\\u1ed5 tay th\\u00e1o r\\u1eddi.\"]','[{\"label\":\"Ch\\u1ea5t li\\u1ec7u\",\"value\":\"Nh\\u1ef1a ABS, b\\u00ecnh PET, gio\\u0103ng silicone\"},{\"label\":\"Dung t\\u00edch\",\"value\":\"350 ml \\u00b7 550 ml\"},{\"label\":\"Ki\\u1ec3u c\\u1ea5p n\\u01b0\\u1edbc\",\"value\":\"N\\u00fat b\\u1ea5m m\\u1ed9t tay\"},{\"label\":\"Ph\\u00f9 h\\u1ee3p\",\"value\":\"Ch\\u00f3 v\\u00e0 m\\u00e8o khi ra ngo\\u00e0i\"}]','Rửa bình và máng sau mỗi ngày sử dụng, để khô khi mở nắp. Không đổ nước nóng hoặc đồ uống khác; kiểm tra khóa gạt trước khi đặt bình vào túi.','[{\"size\":\"350 ml\",\"guidance\":\"G\\u1ecdn cho bu\\u1ed5i \\u0111i d\\u1ea1o ng\\u1eafn ho\\u1eb7c b\\u00e9 d\\u01b0\\u1edbi 8 kg\"},{\"size\":\"550 ml\",\"guidance\":\"Cho h\\u00e0nh tr\\u00ecnh d\\u00e0i h\\u01a1n ho\\u1eb7c b\\u00e9 t\\u1eeb 8 kg\"}]',NULL,966000,NULL,33,'/media/products/pawora-clean/pawora-product-0032.jpg',0.0,0,0,'active','2026-09-26 08:47:32','2026-09-26 08:47:33',NULL);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `return_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `return_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'requested',
  `reason` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `resolution` varchar(255) DEFAULT NULL,
  `refund_amount` decimal(12,0) NOT NULL DEFAULT 0,
  `admin_note` text DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `return_requests_order_id_foreign` (`order_id`),
  KEY `return_requests_user_id_foreign` (`user_id`),
  KEY `return_requests_status_index` (`status`),
  CONSTRAINT `return_requests_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `return_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `return_requests` WRITE;
/*!40000 ALTER TABLE `return_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `return_requests` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `shipments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shipments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `provider` varchar(255) NOT NULL DEFAULT 'manual',
  `tracking_code` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'ready',
  `fee` decimal(12,0) NOT NULL DEFAULT 0,
  `shipped_at` timestamp NULL DEFAULT NULL,
  `customer_received_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `provider_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`provider_payload`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shipments_tracking_code_unique` (`tracking_code`),
  KEY `shipments_order_id_foreign` (`order_id`),
  KEY `shipments_status_index` (`status`),
  CONSTRAINT `shipments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `shipments` WRITE;
/*!40000 ALTER TABLE `shipments` DISABLE KEYS */;
/*!40000 ALTER TABLE `shipments` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `shop_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shop_settings` (
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'string',
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`key`),
  KEY `shop_settings_updated_by_foreign` (`updated_by`),
  CONSTRAINT `shop_settings_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `shop_settings` WRITE;
/*!40000 ALTER TABLE `shop_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `shop_settings` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `user_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_addresses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `label` varchar(255) NOT NULL DEFAULT 'Nhà',
  `receiver_name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `province` varchar(255) NOT NULL,
  `district` varchar(255) DEFAULT NULL,
  `ward` varchar(255) NOT NULL,
  `address_line` varchar(255) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_addresses_user_id_foreign` (`user_id`),
  KEY `user_addresses_is_default_index` (`is_default`),
  CONSTRAINT `user_addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `user_addresses` WRITE;
/*!40000 ALTER TABLE `user_addresses` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_addresses` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `province` varchar(255) DEFAULT NULL,
  `ward` varchar(255) DEFAULT NULL,
  `address_line` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'customer',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `deactivated_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `facebook_id` varchar(255) DEFAULT NULL,
  `avatar_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_google_id_unique` (`google_id`),
  UNIQUE KEY `users_facebook_id_unique` (`facebook_id`),
  KEY `users_role_index` (`role`),
  KEY `users_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Pawora Admin','admin@pawora.vn','0909000001',NULL,NULL,NULL,NULL,'$2y$10$LftHXudH3Bse8vw/R7owMe1OdFNu2UQbBLPkYKusA/0KjagAjvD5q','admin',1,NULL,NULL,'2026-09-26 08:47:32','2026-09-26 08:47:32',NULL,NULL,NULL),(2,'Nguyễn An','khach@pawora.vn','0909000002','Thành phố Hà Nội','Phường Tây Tựu','Số 18, ngõ 5 Ngọa Long',NULL,'$2y$10$JPohpAeAL.NJwoyXksnh2uH7s9woiQ//KE/ELaBVg9eREUIDS/CBm','customer',1,NULL,NULL,'2026-09-26 08:47:32','2026-09-26 08:47:32',NULL,NULL,NULL),(3,'Trần Minh Anh','minhanh.demo@pawora.vn','0911000101','Thành phố Hà Nội','Phường Tây Tựu','18, đường Pawora',NULL,'$2y$10$0PeCxK7FDEBayIMU5Vcouur.jSbFPi9fA34ThljsU05kZkIsdAKpK','customer',1,NULL,NULL,'2026-09-26 08:47:32','2026-09-26 08:47:32',NULL,NULL,NULL),(4,'Lê Hoàng Nam','hoangnam.demo@pawora.vn','0911000102','Thành phố Hồ Chí Minh','Phường Bến Thành','19, đường Pawora',NULL,'$2y$10$sa4fL4Yu8yT.YOfA2DxYuOVSLz4X9/U2YV.AAKsEwzXIBr4FG.B1S','customer',1,NULL,NULL,'2026-09-26 08:47:32','2026-09-26 08:47:32',NULL,NULL,NULL),(5,'Phạm Thu Trang','thutrang.demo@pawora.vn','0911000103','Thành phố Hà Nội','Phường Tây Tựu','20, đường Pawora',NULL,'$2y$10$J6oMud/oueD4Yk8N.RKVEuk7iK9y9.lD.06AI88oq9VHY3kGsQXym','customer',1,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33',NULL,NULL,NULL),(6,'Nguyễn Đức Huy','duchuy.demo@pawora.vn','0911000104','Thành phố Hồ Chí Minh','Phường Bến Thành','21, đường Pawora',NULL,'$2y$10$LnuIGcvQ2y2vTkU5HEtOne5Ud5oyW1W75dG7Ecq7e4wgwY4xWheHO','customer',1,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33',NULL,NULL,NULL),(7,'Đỗ Khánh Linh','khanhlinh.demo@pawora.vn','0911000105','Thành phố Hà Nội','Phường Tây Tựu','22, đường Pawora',NULL,'$2y$10$O7yxPL/8jo7p2GAPqpihQOux7rGbe1AOoPB3Fgz4g/odz8OblcICy','customer',1,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33',NULL,NULL,NULL),(8,'Vũ Quang Minh','quangminh.demo@pawora.vn','0911000106','Thành phố Hồ Chí Minh','Phường Bến Thành','23, đường Pawora',NULL,'$2y$10$aMOfCrUNbOLoR0GxEkZ3z.Sg616C4UrqPykQ8nNlJG14.1.oz06N2','customer',1,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33',NULL,NULL,NULL),(9,'Bùi Ngọc Mai','ngocmai.demo@pawora.vn','0911000107','Thành phố Hà Nội','Phường Tây Tựu','24, đường Pawora',NULL,'$2y$10$M3ZwHyqBFMkIjkbCYwm7zOASs6LQZkOup1WoJturGknWyFu4yeM5.','customer',1,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33',NULL,NULL,NULL),(10,'Hoàng Gia Bảo','giabao.demo@pawora.vn','0911000108','Thành phố Hồ Chí Minh','Phường Bến Thành','25, đường Pawora',NULL,'$2y$10$lgwuc50PyfHBQDF15k3tpejO9LO0HVLtg.SgzqRxvzl/6nbphwWjC','customer',1,NULL,NULL,'2026-09-26 08:47:33','2026-09-26 08:47:33',NULL,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

