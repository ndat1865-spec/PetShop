
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
INSERT INTO `categories` VALUES (1,NULL,'Đồ chơi cho chó','do-choi-cho-cho','Phụ kiện được Pawora tuyển chọn cho những khoảnh khắc gần gũi mỗi ngày.',NULL,1,'2026-08-31 06:49:57','2026-08-31 06:49:57',NULL),(2,NULL,'Đồ chơi cho mèo','do-choi-cho-meo','Phụ kiện được Pawora tuyển chọn cho những khoảnh khắc gần gũi mỗi ngày.',NULL,1,'2026-08-31 06:49:57','2026-08-31 06:49:57',NULL),(3,NULL,'Vòng cổ & dây dắt','vong-co-day-dat','Phụ kiện được Pawora tuyển chọn cho những khoảnh khắc gần gũi mỗi ngày.',NULL,1,'2026-08-31 06:49:57','2026-08-31 06:49:57',NULL),(4,NULL,'Giường & chăn','giuong-chan','Phụ kiện được Pawora tuyển chọn cho những khoảnh khắc gần gũi mỗi ngày.',NULL,1,'2026-08-31 06:49:57','2026-08-31 06:49:57',NULL),(5,NULL,'Bát ăn & bình nước','bat-an-binh-nuoc','Phụ kiện được Pawora tuyển chọn cho những khoảnh khắc gần gũi mỗi ngày.',NULL,1,'2026-08-31 06:49:57','2026-08-31 06:49:57',NULL),(6,NULL,'Grooming','grooming','Phụ kiện được Pawora tuyển chọn cho những khoảnh khắc gần gũi mỗi ngày.',NULL,1,'2026-08-31 06:49:57','2026-08-31 06:49:57',NULL),(7,NULL,'Quần áo thú cưng','quan-ao-thu-cung','Phụ kiện được Pawora tuyển chọn cho những khoảnh khắc gần gũi mỗi ngày.',NULL,1,'2026-08-31 06:49:57','2026-08-31 06:49:57',NULL),(8,NULL,'Phụ kiện du lịch','phu-kien-du-lich','Phụ kiện được Pawora tuyển chọn cho những khoảnh khắc gần gũi mỗi ngày.',NULL,1,'2026-08-31 06:49:57','2026-08-31 06:49:57',NULL);
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
  PRIMARY KEY (`id`),
  KEY `chat_auto_reply_rules_created_by_foreign` (`created_by`),
  KEY `chat_auto_reply_rules_updated_by_foreign` (`updated_by`),
  KEY `chat_auto_reply_rules_active_priority_index` (`active`,`priority`),
  CONSTRAINT `chat_auto_reply_rules_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chat_auto_reply_rules_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `chat_auto_reply_rules` WRITE;
/*!40000 ALTER TABLE `chat_auto_reply_rules` DISABLE KEYS */;
INSERT INTO `chat_auto_reply_rules` VALUES (1,'Lời chào','[\"xin ch\\u00e0o\",\"hello\",\"ch\\u00e0o shop\"]','contains','Xin chào! Pawora đã nhận được tin nhắn. Bạn cần hỗ trợ sản phẩm hay đơn hàng ạ?',100,0,1,1,'always',NULL,NULL,NULL,1,4,1,1,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(2,'Phí giao hàng','[\"ph\\u00ed ship\",\"ph\\u00ed v\\u1eadn chuy\\u1ec3n\",\"giao h\\u00e0ng\"]','contains','Pawora tính phí theo khu vực và sẽ hiển thị chính xác ở bước thanh toán. Đơn đủ điều kiện sẽ được miễn phí vận chuyển.',90,1,1,1,'always',NULL,NULL,NULL,1,5,1,1,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(3,'Đổi trả và hoàn tiền','[\"\\u0111\\u1ed5i tr\\u1ea3\",\"ho\\u00e0n ti\\u1ec1n\",\"tr\\u1ea3 h\\u00e0ng\"]','contains','Pawora hỗ trợ yêu cầu đổi trả trong vòng 7 ngày kể từ khi nhận hàng. Bạn gửi giúp mã đơn và tình trạng sản phẩm nhé.',85,2,1,1,'always',NULL,NULL,NULL,1,6,1,1,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(4,'Kiểm tra đơn hàng','[\"m\\u00e3 v\\u1eadn \\u0111\\u01a1n\",\"\\u0111\\u01a1n h\\u00e0ng c\\u1ee7a t\\u00f4i\",\"ki\\u1ec3m tra \\u0111\\u01a1n\"]','contains','Bạn gửi mã đơn hàng để Pawora kiểm tra hành trình và mã vận đơn chính xác giúp bạn nhé.',95,0,1,1,'always',NULL,NULL,NULL,1,7,1,1,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(5,'Tư vấn sản phẩm','[\"t\\u01b0 v\\u1ea5n s\\u1ea3n ph\\u1ea9m\",\"ch\\u1ecdn th\\u1ee9c \\u0103n\",\"ch\\u1ecdn size\"]','contains','Bạn cho Pawora biết loại thú cưng, cân nặng và nhu cầu sử dụng. Nhân viên sẽ tư vấn lựa chọn phù hợp nhất.',80,1,1,1,'always',NULL,NULL,NULL,1,8,1,1,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(6,'Ngoài giờ hỗ trợ','[\"h\\u1ed7 tr\\u1ee3\",\"nh\\u00e2n vi\\u00ean\",\"t\\u01b0 v\\u1ea5n\"]','contains','Pawora đã ghi nhận yêu cầu ngoài giờ. Đội ngũ sẽ phản hồi từ 08:00 đến 18:00 trong ngày làm việc gần nhất.',30,2,1,1,'outside_hours','08:00:00','18:00:00','[1,2,3,4,5,6]',1,9,1,1,'2026-08-31 06:49:58','2026-08-31 06:49:58');
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `chat_conversations_uuid_unique` (`uuid`),
  UNIQUE KEY `chat_conversations_channel_key_unique` (`channel_key`),
  KEY `chat_conversations_status_last_message_at_index` (`status`,`last_message_at`),
  KEY `chat_conversations_assigned_to_status_index` (`assigned_to`,`status`),
  KEY `chat_conversations_user_id_last_message_at_index` (`user_id`,`last_message_at`),
  KEY `chat_conversations_order_number_index` (`order_number`),
  CONSTRAINT `chat_conversations_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chat_conversations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `chat_conversations` WRITE;
/*!40000 ALTER TABLE `chat_conversations` DISABLE KEYS */;
INSERT INTO `chat_conversations` VALUES (1,'2544a548-6249-4f97-9794-8208ccfb66aa','b9e4af3f185c2d72a75654a285ae8adb4d99e6f51bc5c61c708dc0c5ce72ade9','dbaa8d3d3e804fe04159b29373a54040809274f16fd2f8cd9dac12df07d5f2e4',3,'Trần Minh Anh','minhanh.demo@pawora.vn','0911000101','PAW-DEMO-RV-01',NULL,'new','high',1,0,1,'2026-08-30 20:49:58',NULL,NULL,NULL,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(2,'8d74646d-3cd0-4299-88f7-df88ba261cda','d3873047933decdd3eae53f4c401cac549905cfb7e45d20bc524c3c2939e600e','8cc24fb1962d94b84d46c13f551fd1470646a4baf7f33ff190d8973835059253',4,'Lê Hoàng Nam','hoangnam.demo@pawora.vn','0911000102','PAW-DEMO-RV-02',1,'open','normal',0,1,1,'2026-08-30 21:49:58',NULL,NULL,NULL,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(3,'45e4d519-e743-4d6e-8a04-8e21aa9e1036','3d5e7626c2290e8171cda0dc14b157bbc380e861b2b1f421d8f9debc45a86b5d','3a43a9e37ae317c3815561e57d794fe9966f1ba9806b9e5c38d947926c3e8a50',5,'Phạm Thu Trang','thutrang.demo@pawora.vn','0911000103','PAW-DEMO-RV-03',1,'waiting_customer','normal',0,1,0,'2026-08-30 22:49:58',NULL,NULL,NULL,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(4,'964063f8-8172-458a-8878-1640d63711c5','0ce9988f5edc5c7d522cc3a1ea59e42d13e1e3147f7e0b1644f3e651b8071ff2','306704e41edc4812787b9c36598e575bd5be8b442f53507edc1cf1da0c2709ac',6,'Nguyễn Đức Huy','duchuy.demo@pawora.vn','0911000104','PAW-DEMO-RV-04',1,'resolved','normal',0,1,0,'2026-08-30 23:49:58',NULL,NULL,NULL,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(5,'a8e0d914-e8ca-47ad-97e7-ba4e300221aa','c926a614d6512850194fe2b6af5aee8e4ddf8bc5ebfb60d553bfc363aff0dd43','078e71b30ee565c83c17722aea8e5c8ac8c87a3431e47cdc331ae2f0742e8585',7,'Đỗ Khánh Linh','khanhlinh.demo@pawora.vn','0911000105','PAW-DEMO-RV-05',NULL,'new','urgent',1,0,1,'2026-08-31 00:49:58',NULL,NULL,NULL,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(6,'5f073853-e3d4-47bf-bb5b-c0aa23b1f22f','e03297d11980484fe95d296cc47010f015c784e336b58e4c4c1f44e4b45598e8','539141755c03d4a52df70e545642a03e1ee9e085f670b7197ce329109c5d60cc',8,'Vũ Quang Minh','quangminh.demo@pawora.vn','0911000106','PAW-DEMO-RV-06',1,'open','high',0,1,1,'2026-08-31 01:49:58',NULL,NULL,NULL,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(7,'8ed446f4-7d80-4188-83ea-bd52b2365f68','d4886ab096fecabc8382c927d15743d9cc1535298edba25fc833fd9d0a74e235','9c2ba885f44acfe23d264c8e0143b53bb9e11ee8e4544f9d38712f4081239eaf',9,'Bùi Ngọc Mai','ngocmai.demo@pawora.vn','0911000107','PAW-DEMO-RV-07',1,'closed','normal',0,1,0,'2026-08-31 02:49:58',NULL,NULL,'2026-08-31 04:49:58','2026-08-31 06:49:58','2026-08-31 06:49:58'),(8,'bd8b63b4-209e-4ecf-a3f2-87aeabe4bf7f','ed90a1ba934e16dfecfde1cbfbdb552b148dc7db800273a749c03bfd39316d64','c2fb013741930f96b0eb7bb156b7da8122a28048d97a4b1efbb2a2f981432002',10,'Hoàng Gia Bảo','giabao.demo@pawora.vn','0911000108','PAW-DEMO-RV-08',NULL,'new','normal',1,0,1,'2026-08-31 03:49:58',NULL,NULL,NULL,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(9,'a2f6aaf1-915b-4987-be82-1d55590922fa','efe32f7cadde7369015fd96f10e634fb2d679f4fb3cab66b2453608ed98ddb1b','8832d02594f77fdc702d7a5620491c03c52bf8048111ffb1c8fa1dba18003ce1',NULL,'Nguyễn Hải Yến','haiyen.chat@pawora.vn','0938111222',NULL,1,'waiting_customer','normal',0,1,0,'2026-08-31 04:49:58',NULL,NULL,NULL,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(10,'d99a30a5-9562-4ffe-a23e-7afd880ed8e8','fc2c093634ddcc9ff161831ee26cb1f2fcd39223f34aeaca68adaa5acc14912f','1d734d6f0a785b41a25b92b97e560116fc0bd0e3c71753c9f4a45faa23b95262',NULL,'Demo Spam','spam.chat@pawora.vn','0900000999',NULL,NULL,'spam','normal',1,0,0,'2026-08-31 05:49:58',NULL,NULL,'2026-08-31 04:49:58','2026-08-31 06:49:58','2026-08-31 06:49:58');
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
INSERT INTO `chat_messages` VALUES (1,1,'pawora-demo-customer-0','customer',3,NULL,'text','Mình muốn kiểm tra đơn hàng của tôi đã có mã vận đơn chưa?',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-30 18:49:58','2026-08-30 18:49:58'),(2,1,NULL,'bot',NULL,4,'text','Bạn gửi mã đơn hàng để Pawora kiểm tra hành trình và mã vận đơn chính xác giúp bạn nhé.',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(3,2,'pawora-demo-customer-1','customer',4,NULL,'text','Pawora tư vấn giúp mình chọn size yếm cho bé 9kg nhé.',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-30 19:49:58','2026-08-30 19:49:58'),(4,2,NULL,'admin',1,NULL,'text','Mình đã nhận thông tin. Bạn đo thêm vòng ngực của bé giúp Pawora nhé.',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(5,3,'pawora-demo-customer-2','customer',5,NULL,'text','Sản phẩm có đổi sang màu khác được không ạ?',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-30 20:49:58','2026-08-30 20:49:58'),(6,3,NULL,'admin',1,NULL,'text','Được bạn nhé. Bạn cho Pawora biết màu muốn đổi và tình trạng sản phẩm hiện tại.',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(7,4,'pawora-demo-customer-3','customer',6,NULL,'text','Mình cần hướng dẫn vệ sinh giường cho thú cưng.',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-30 21:49:58','2026-08-30 21:49:58'),(8,4,NULL,'admin',1,NULL,'text','Pawora đã gửi hướng dẫn bảo quản. Bạn có thể giặt phần vỏ ở chế độ nhẹ và phơi nơi thoáng mát.',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(9,5,'pawora-demo-customer-4','customer',7,NULL,'text','Mình đã thanh toán nhưng đơn vẫn hiện đang chờ.',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-30 22:49:58','2026-08-30 22:49:58'),(10,6,'pawora-demo-customer-5','customer',8,NULL,'text','Mã vận đơn hai ngày rồi chưa cập nhật hành trình.',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-30 23:49:58','2026-08-30 23:49:58'),(11,6,NULL,'admin',1,NULL,'text','Pawora đang đối soát với đơn vị vận chuyển và sẽ cập nhật lại cho bạn sớm nhất.',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(12,7,'pawora-demo-customer-6','customer',9,NULL,'text','Cảm ơn shop, mình đã nhận được hàng rồi.',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-31 00:49:58','2026-08-31 00:49:58'),(13,7,NULL,'admin',1,NULL,'text','Cảm ơn bạn đã tin chọn Pawora. Chúc bé có thật nhiều trải nghiệm vui vẻ!',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(14,8,'pawora-demo-customer-7','customer',10,NULL,'text','Phí ship về Hà Nội hiện tại là bao nhiêu?',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-31 01:49:58','2026-08-31 01:49:58'),(15,8,NULL,'bot',NULL,2,'text','Pawora tính phí theo khu vực và sẽ hiển thị chính xác ở bước thanh toán. Đơn đủ điều kiện sẽ được miễn phí vận chuyển.',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(16,9,'pawora-demo-customer-8','customer',NULL,NULL,'text','Mình muốn hỏi về chính sách hoàn tiền.',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-31 02:49:58','2026-08-31 02:49:58'),(17,9,NULL,'admin',1,NULL,'text','Bạn gửi giúp Pawora mã đơn và ảnh tình trạng sản phẩm để đội ngũ kiểm tra nhé.',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(18,10,'pawora-demo-customer-9','customer',NULL,NULL,'text','Nội dung quảng cáo không liên quan.',NULL,NULL,NULL,NULL,NULL,NULL,'2026-08-31 03:49:58','2026-08-31 03:49:58');
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
INSERT INTO `contact_messages` VALUES (1,NULL,'Lan Anh','lananh.contact@pawora.vn','0922000001','order',NULL,'Mình muốn kiểm tra lại thời gian giao dự kiến của đơn hàng vì tuần tới gia đình có lịch đi xa.','new',NULL,NULL,NULL,'2026-08-25 06:49:58','2026-08-26 06:49:58'),(2,NULL,'Minh Khoa','minhkhoa.contact@pawora.vn','0922000002','product',NULL,'Cho mình xin tư vấn kích thước phù hợp cho bé cún nặng khoảng tám ký và vòng ngực bốn mươi lăm centimet.','in_progress',NULL,NULL,NULL,'2026-08-26 06:49:58','2026-08-27 06:49:58'),(3,NULL,'Thu Hà','thuha.contact@pawora.vn','0922000003','returns',NULL,'Sản phẩm mình nhận còn nguyên nhưng kích thước chưa phù hợp, nhờ Pawora hướng dẫn quy trình đổi sang size khác.','resolved',NULL,'Đã hướng dẫn khách đổi kích thước.','2026-08-28 06:49:58','2026-08-27 06:49:58','2026-08-28 06:49:58'),(4,NULL,'Quốc Bảo','quocbao.contact@pawora.vn','0922000004','shipping',NULL,'Mã vận đơn đã được tạo nhưng hai ngày chưa cập nhật hành trình, nhờ đội ngũ kiểm tra giúp mình.','new',NULL,NULL,NULL,'2026-08-28 06:49:58','2026-08-29 06:49:58'),(5,NULL,'Mai Phương','maiphuong.contact@pawora.vn','0922000005','payment',NULL,'Mình đã thanh toán qua ngân hàng nhưng trạng thái đơn vẫn đang chờ, vui lòng hỗ trợ đối soát giao dịch.','new',NULL,NULL,NULL,'2026-08-29 06:49:58','2026-08-30 06:49:58'),(6,NULL,'Demo Spam','spam.contact@pawora.vn','0922000006','other',NULL,'Nội dung quảng cáo không liên quan đến sản phẩm hoặc dịch vụ chăm sóc khách hàng của Pawora.','spam',NULL,NULL,NULL,'2026-08-30 06:49:58','2026-08-31 06:49:58');
/*!40000 ALTER TABLE `contact_messages` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `coupons` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'percent',
  `value` decimal(12,0) NOT NULL,
  `minimum_order` decimal(12,0) NOT NULL DEFAULT 0,
  `starts_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `usage_limit` int(10) unsigned DEFAULT NULL,
  `used_count` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `coupons_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `coupons` WRITE;
/*!40000 ALTER TABLE `coupons` DISABLE KEYS */;
INSERT INTO `coupons` VALUES (1,'PAWORA10','percent',10,500000,NULL,NULL,500,0,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(2,'BEMOI50','fixed',50000,300000,NULL,NULL,300,0,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(3,'PAWORA15','percent',15,800000,NULL,NULL,200,0,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(4,'GIAM100K','fixed',100000,1200000,NULL,NULL,100,0,1,'2026-08-31 06:49:57','2026-08-31 06:49:57');
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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_reset_tokens_table',1),(3,'2019_08_19_000000_create_failed_jobs_table',1),(4,'2019_12_14_000001_create_personal_access_tokens_table',1),(5,'2026_08_19_000001_create_pawora_commerce_tables',1),(6,'2026_08_19_000002_add_structured_shipping_address_to_orders',1),(7,'2026_08_19_000003_add_default_address_to_users',1),(8,'2026_08_19_000005_add_production_commerce_foundation',1),(9,'2026_08_19_000006_create_user_addresses_table',1),(10,'2026_08_21_000001_add_social_ids_to_users_table',1),(11,'2026_08_22_000001_add_avatar_path_to_users_table',1),(12,'2026_08_23_000001_add_product_variant_to_order_items',1),(13,'2026_08_27_000001_add_customer_received_at_to_shipments',1),(14,'2026_08_27_000001_create_admin_audit_logs_table',1),(15,'2026_08_27_000002_create_inventory_adjustments_table',1),(16,'2026_08_27_000003_add_admin_operational_controls',1),(17,'2026_08_29_000001_create_product_reviews_table',1),(18,'2026_08_29_000002_add_moderation_to_product_reviews',1),(19,'2026_08_29_000003_create_contact_messages_table',1),(20,'2026_08_29_000004_create_customer_chat_tables',1),(21,'2026_08_31_000001_add_checkout_token_to_orders',1);
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
INSERT INTO `order_items` VALUES (1,1,1,NULL,'Bóng cao su Pounce','PAW-0001',129000,1,129000,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(2,1,5,NULL,'Cần câu lông vũ Miso','PAW-0005',237000,1,237000,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(3,1,9,NULL,'Vòng cổ AirTag Linen','PAW-0009',345000,1,345000,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(4,2,2,NULL,'Dây thừng Tug & Joy','PAW-0002',156000,1,156000,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(5,2,6,NULL,'Bóng len Moon Ball','PAW-0006',264000,1,264000,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(6,2,10,NULL,'Dây dắt Urban Walk','PAW-0010',372000,1,372000,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(7,3,3,NULL,'Đĩa bay Flexi Disc','PAW-0003',183000,1,183000,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(8,3,7,NULL,'Bàn cào móng Cloud','PAW-0007',291000,1,291000,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(9,3,11,NULL,'Yếm ngực Comfort Pro','PAW-0011',399000,1,399000,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(10,4,4,NULL,'Đồ chơi phát tiếng Nibble','PAW-0004',210000,1,210000,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(11,4,8,NULL,'Đường hầm Hide & Seek','PAW-0008',318000,1,318000,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(12,4,12,NULL,'Bộ dây dắt Weekend','PAW-0012',426000,1,426000,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(13,5,5,NULL,'Cần câu lông vũ Miso','PAW-0005',237000,1,237000,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(14,5,9,NULL,'Vòng cổ AirTag Linen','PAW-0009',345000,1,345000,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(15,5,13,NULL,'Giường ngủ Cocoon','PAW-0013',453000,1,453000,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(16,6,6,NULL,'Bóng len Moon Ball','PAW-0006',264000,1,264000,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(17,6,10,NULL,'Dây dắt Urban Walk','PAW-0010',372000,1,372000,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(18,6,14,NULL,'Nệm mây Soft Nest','PAW-0014',480000,1,480000,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(19,7,7,NULL,'Bàn cào móng Cloud','PAW-0007',291000,1,291000,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(20,7,11,NULL,'Yếm ngực Comfort Pro','PAW-0011',399000,1,399000,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(21,7,15,NULL,'Chăn cotton Calm','PAW-0015',507000,1,507000,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(22,8,8,NULL,'Đường hầm Hide & Seek','PAW-0008',318000,1,318000,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(23,8,12,NULL,'Bộ dây dắt Weekend','PAW-0012',426000,1,426000,'2026-08-31 06:49:58','2026-08-31 06:49:58'),(24,8,16,NULL,'Nhà vải Hideaway','PAW-0016',534000,1,534000,'2026-08-31 06:49:58','2026-08-31 06:49:58');
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
  KEY `orders_user_id_foreign` (`user_id`),
  KEY `orders_payment_status_index` (`payment_status`),
  KEY `orders_status_index` (`status`),
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,'PAW-DEMO-RV-01',NULL,NULL,3,'Trần Minh Anh','minhanh.demo@pawora.vn','0911000101',NULL,NULL,NULL,NULL,'18, đường Pawora, Phường Tây Tựu, Thành phố Hà Nội',NULL,NULL,711000,0,0,NULL,NULL,711000,'VND','cod','paid','2026-05-03 06:49:57',0,'delivered','2026-04-29 06:49:57','2026-05-05 06:49:57'),(2,'PAW-DEMO-RV-02',NULL,NULL,4,'Lê Hoàng Nam','hoangnam.demo@pawora.vn','0911000102',NULL,NULL,NULL,NULL,'19, đường Pawora, Phường Bến Thành, Thành phố Hồ Chí Minh',NULL,NULL,792000,0,0,NULL,NULL,792000,'VND','cod','paid','2026-05-02 06:49:57',0,'delivered','2026-04-28 06:49:57','2026-05-04 06:49:57'),(3,'PAW-DEMO-RV-03',NULL,NULL,5,'Phạm Thu Trang','thutrang.demo@pawora.vn','0911000103',NULL,NULL,NULL,NULL,'20, đường Pawora, Phường Tây Tựu, Thành phố Hà Nội',NULL,NULL,873000,0,0,NULL,NULL,873000,'VND','cod','paid','2026-05-01 06:49:57',0,'delivered','2026-04-27 06:49:57','2026-05-03 06:49:57'),(4,'PAW-DEMO-RV-04',NULL,NULL,6,'Nguyễn Đức Huy','duchuy.demo@pawora.vn','0911000104',NULL,NULL,NULL,NULL,'21, đường Pawora, Phường Bến Thành, Thành phố Hồ Chí Minh',NULL,NULL,954000,0,0,NULL,NULL,954000,'VND','cod','paid','2026-04-30 06:49:58',0,'delivered','2026-04-26 06:49:58','2026-05-02 06:49:58'),(5,'PAW-DEMO-RV-05',NULL,NULL,7,'Đỗ Khánh Linh','khanhlinh.demo@pawora.vn','0911000105',NULL,NULL,NULL,NULL,'22, đường Pawora, Phường Tây Tựu, Thành phố Hà Nội',NULL,NULL,1035000,0,0,NULL,NULL,1035000,'VND','cod','paid','2026-04-29 06:49:58',0,'delivered','2026-04-25 06:49:58','2026-05-01 06:49:58'),(6,'PAW-DEMO-RV-06',NULL,NULL,8,'Vũ Quang Minh','quangminh.demo@pawora.vn','0911000106',NULL,NULL,NULL,NULL,'23, đường Pawora, Phường Bến Thành, Thành phố Hồ Chí Minh',NULL,NULL,1116000,0,0,NULL,NULL,1116000,'VND','cod','paid','2026-04-28 06:49:58',0,'delivered','2026-04-24 06:49:58','2026-04-30 06:49:58'),(7,'PAW-DEMO-RV-07',NULL,NULL,9,'Bùi Ngọc Mai','ngocmai.demo@pawora.vn','0911000107',NULL,NULL,NULL,NULL,'24, đường Pawora, Phường Tây Tựu, Thành phố Hà Nội',NULL,NULL,1197000,0,0,NULL,NULL,1197000,'VND','cod','paid','2026-04-27 06:49:58',0,'delivered','2026-04-23 06:49:58','2026-04-29 06:49:58'),(8,'PAW-DEMO-RV-08',NULL,NULL,10,'Hoàng Gia Bảo','giabao.demo@pawora.vn','0911000108',NULL,NULL,NULL,NULL,'25, đường Pawora, Phường Bến Thành, Thành phố Hồ Chí Minh',NULL,NULL,1278000,0,0,NULL,NULL,1278000,'VND','cod','paid','2026-04-26 06:49:58',0,'delivered','2026-04-22 06:49:58','2026-04-28 06:49:58');
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
  `title` varchar(100) DEFAULT NULL,
  `comment` text NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'approved',
  `admin_note` text DEFAULT NULL,
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
  CONSTRAINT `product_reviews_moderated_by_foreign` FOREIGN KEY (`moderated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `product_reviews_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `product_reviews_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `product_reviews` WRITE;
/*!40000 ALTER TABLE `product_reviews` DISABLE KEYS */;
INSERT INTO `product_reviews` VALUES (1,1,3,1,5,'Bé nhà mình rất thích','Sản phẩm hoàn thiện tốt, bề mặt dễ vệ sinh và bé nhà mình làm quen rất nhanh sau khi mở hộp.','approved',NULL,1,'2026-07-02 06:49:57','2026-06-07 06:49:57','2026-06-08 06:49:57'),(2,5,3,1,4,'Chất liệu chắc chắn','Mình đã dùng hơn hai tuần, đường may và các chi tiết vẫn chắc chắn, màu sắc giống hình trên trang.','approved',NULL,1,'2026-07-01 06:49:57','2026-06-08 06:49:57','2026-06-09 06:49:57'),(3,9,3,1,5,'Đúng mô tả và dễ dùng','Kích thước đúng với phần mô tả, thao tác sử dụng đơn giản và không gây khó chịu cho thú cưng.','approved',NULL,1,'2026-06-30 06:49:57','2026-06-09 06:49:57','2026-06-10 06:49:57'),(4,2,4,2,4,'Chất liệu chắc chắn','Mình đã dùng hơn hai tuần, đường may và các chi tiết vẫn chắc chắn, màu sắc giống hình trên trang.','approved',NULL,1,'2026-06-29 06:49:57','2026-06-10 06:49:57','2026-06-11 06:49:57'),(5,6,4,2,5,'Đúng mô tả và dễ dùng','Kích thước đúng với phần mô tả, thao tác sử dụng đơn giản và không gây khó chịu cho thú cưng.','approved',NULL,1,'2026-06-28 06:49:57','2026-06-11 06:49:57','2026-06-12 06:49:57'),(6,10,4,2,4,'Thiết kế đẹp, đóng gói kỹ','Pawora đóng gói cẩn thận, sản phẩm không có mùi lạ và có hướng dẫn bảo quản khá rõ ràng.','pending',NULL,NULL,NULL,'2026-06-12 06:49:57','2026-06-13 06:49:57'),(7,3,5,3,5,'Đúng mô tả và dễ dùng','Kích thước đúng với phần mô tả, thao tác sử dụng đơn giản và không gây khó chịu cho thú cưng.','approved',NULL,1,'2026-06-26 06:49:57','2026-06-13 06:49:57','2026-06-14 06:49:57'),(8,7,5,3,4,'Thiết kế đẹp, đóng gói kỹ','Pawora đóng gói cẩn thận, sản phẩm không có mùi lạ và có hướng dẫn bảo quản khá rõ ràng.','approved',NULL,1,'2026-06-25 06:49:57','2026-06-14 06:49:57','2026-06-15 06:49:57'),(9,11,5,3,5,'Phù hợp dùng hằng ngày','Thiết kế gọn, đặt trong nhà không chiếm nhiều diện tích và chất liệu cho cảm giác bền khi sử dụng.','approved',NULL,1,'2026-06-24 06:49:57','2026-06-15 06:49:57','2026-06-16 06:49:57'),(10,4,6,4,4,'Thiết kế đẹp, đóng gói kỹ','Pawora đóng gói cẩn thận, sản phẩm không có mùi lạ và có hướng dẫn bảo quản khá rõ ràng.','approved',NULL,1,'2026-06-23 06:49:58','2026-06-16 06:49:58','2026-06-17 06:49:58'),(11,8,6,4,5,'Phù hợp dùng hằng ngày','Thiết kế gọn, đặt trong nhà không chiếm nhiều diện tích và chất liệu cho cảm giác bền khi sử dụng.','approved',NULL,1,'2026-06-22 06:49:58','2026-06-17 06:49:58','2026-06-18 06:49:58'),(12,12,6,4,3,'Kích thước vừa vặn','Mình chọn theo bảng kích thước và nhận được sản phẩm vừa vặn, bé vận động vẫn khá thoải mái.','hidden','Tạm ẩn để kiểm tra thông tin mô tả.',1,'2026-06-21 06:49:58','2026-06-18 06:49:58','2026-06-19 06:49:58'),(13,5,7,5,5,'Phù hợp dùng hằng ngày','Thiết kế gọn, đặt trong nhà không chiếm nhiều diện tích và chất liệu cho cảm giác bền khi sử dụng.','approved',NULL,1,'2026-06-20 06:49:58','2026-06-19 06:49:58','2026-06-20 06:49:58'),(14,9,7,5,3,'Kích thước vừa vặn','Mình chọn theo bảng kích thước và nhận được sản phẩm vừa vặn, bé vận động vẫn khá thoải mái.','approved',NULL,1,'2026-06-19 06:49:58','2026-06-20 06:49:58','2026-06-21 06:49:58'),(15,13,7,5,4,'Trải nghiệm ở mức khá','Chất lượng nhìn chung ổn nhưng màu thực tế trầm hơn ảnh một chút, phần còn lại đúng như mô tả.','approved',NULL,1,'2026-06-18 06:49:58','2026-06-21 06:49:58','2026-06-22 06:49:58'),(16,6,8,6,3,'Kích thước vừa vặn','Mình chọn theo bảng kích thước và nhận được sản phẩm vừa vặn, bé vận động vẫn khá thoải mái.','approved',NULL,1,'2026-06-17 06:49:58','2026-06-22 06:49:58','2026-06-23 06:49:58'),(17,10,8,6,4,'Trải nghiệm ở mức khá','Chất lượng nhìn chung ổn nhưng màu thực tế trầm hơn ảnh một chút, phần còn lại đúng như mô tả.','approved',NULL,1,'2026-06-16 06:49:58','2026-06-23 06:49:58','2026-06-24 06:49:58'),(18,14,8,6,2,'Dễ vệ sinh hơn mình nghĩ','Sau vài lần vệ sinh sản phẩm vẫn giữ được dáng, đây là điểm mình hài lòng nhất khi sử dụng.','pending',NULL,NULL,NULL,'2026-06-24 06:49:58','2026-06-25 06:49:58'),(19,7,9,7,4,'Trải nghiệm ở mức khá','Chất lượng nhìn chung ổn nhưng màu thực tế trầm hơn ảnh một chút, phần còn lại đúng như mô tả.','approved',NULL,1,'2026-06-14 06:49:58','2026-06-25 06:49:58','2026-06-26 06:49:58'),(20,11,9,7,2,'Dễ vệ sinh hơn mình nghĩ','Sau vài lần vệ sinh sản phẩm vẫn giữ được dáng, đây là điểm mình hài lòng nhất khi sử dụng.','hidden','Tạm ẩn để kiểm tra thông tin mô tả.',1,'2026-06-13 06:49:58','2026-06-26 06:49:58','2026-06-27 06:49:58'),(21,15,9,7,5,'Bé nhà mình rất thích','Sản phẩm hoàn thiện tốt, bề mặt dễ vệ sinh và bé nhà mình làm quen rất nhanh sau khi mở hộp.','approved',NULL,1,'2026-06-12 06:49:58','2026-06-27 06:49:58','2026-06-28 06:49:58'),(22,8,10,8,2,'Dễ vệ sinh hơn mình nghĩ','Sau vài lần vệ sinh sản phẩm vẫn giữ được dáng, đây là điểm mình hài lòng nhất khi sử dụng.','pending',NULL,NULL,NULL,'2026-06-28 06:49:58','2026-06-29 06:49:58'),(23,12,10,8,5,'Bé nhà mình rất thích','Sản phẩm hoàn thiện tốt, bề mặt dễ vệ sinh và bé nhà mình làm quen rất nhanh sau khi mở hộp.','approved',NULL,1,'2026-06-10 06:49:58','2026-06-29 06:49:58','2026-06-30 06:49:58'),(24,16,10,8,4,'Chất liệu chắc chắn','Mình đã dùng hơn hai tuần, đường may và các chi tiết vẫn chắc chắn, màu sắc giống hình trên trang.','rejected','Nội dung chưa đủ thông tin về trải nghiệm sản phẩm.',1,'2026-06-09 06:49:58','2026-06-30 06:49:58','2026-07-01 06:49:58');
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
INSERT INTO `product_variants` VALUES (1,1,'Xanh lá · Nhỏ','PAW-0001-1-1','Nhỏ','Xanh lá',129000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(2,1,'Xanh lá · Lớn','PAW-0001-1-2','Lớn','Xanh lá',159000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(3,1,'Vàng · Nhỏ','PAW-0001-2-1','Nhỏ','Vàng',129000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(4,1,'Vàng · Lớn','PAW-0001-2-2','Lớn','Vàng',159000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(5,1,'Cam · Nhỏ','PAW-0001-3-1','Nhỏ','Cam',129000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(6,1,'Cam · Lớn','PAW-0001-3-2','Lớn','Cam',159000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(7,2,'Xanh lá · Nhỏ','PAW-0002-1-1','Nhỏ','Xanh lá',156000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(8,2,'Xanh lá · Lớn','PAW-0002-1-2','Lớn','Xanh lá',186000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(9,2,'Vàng · Nhỏ','PAW-0002-2-1','Nhỏ','Vàng',156000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(10,2,'Vàng · Lớn','PAW-0002-2-2','Lớn','Vàng',186000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(11,2,'Cam · Nhỏ','PAW-0002-3-1','Nhỏ','Cam',156000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(12,2,'Cam · Lớn','PAW-0002-3-2','Lớn','Cam',186000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(13,3,'Xanh lá · Nhỏ','PAW-0003-1-1','Nhỏ','Xanh lá',183000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(14,3,'Xanh lá · Lớn','PAW-0003-1-2','Lớn','Xanh lá',213000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(15,3,'Vàng · Nhỏ','PAW-0003-2-1','Nhỏ','Vàng',183000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(16,3,'Vàng · Lớn','PAW-0003-2-2','Lớn','Vàng',213000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(17,3,'Cam · Nhỏ','PAW-0003-3-1','Nhỏ','Cam',183000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(18,3,'Cam · Lớn','PAW-0003-3-2','Lớn','Cam',213000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(19,4,'Xanh lá · Nhỏ','PAW-0004-1-1','Nhỏ','Xanh lá',210000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(20,4,'Xanh lá · Lớn','PAW-0004-1-2','Lớn','Xanh lá',240000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(21,4,'Vàng · Nhỏ','PAW-0004-2-1','Nhỏ','Vàng',210000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(22,4,'Vàng · Lớn','PAW-0004-2-2','Lớn','Vàng',240000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(23,4,'Cam · Nhỏ','PAW-0004-3-1','Nhỏ','Cam',210000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(24,4,'Cam · Lớn','PAW-0004-3-2','Lớn','Cam',240000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(25,5,'Hồng · Nhỏ','PAW-0005-1-1','Nhỏ','Hồng',237000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(26,5,'Hồng · Lớn','PAW-0005-1-2','Lớn','Hồng',267000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(27,5,'Xanh dương · Nhỏ','PAW-0005-2-1','Nhỏ','Xanh dương',237000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(28,5,'Xanh dương · Lớn','PAW-0005-2-2','Lớn','Xanh dương',267000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(29,5,'Tím · Nhỏ','PAW-0005-3-1','Nhỏ','Tím',237000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(30,5,'Tím · Lớn','PAW-0005-3-2','Lớn','Tím',267000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(31,6,'Hồng · Nhỏ','PAW-0006-1-1','Nhỏ','Hồng',264000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(32,6,'Hồng · Lớn','PAW-0006-1-2','Lớn','Hồng',294000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(33,6,'Xanh dương · Nhỏ','PAW-0006-2-1','Nhỏ','Xanh dương',264000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(34,6,'Xanh dương · Lớn','PAW-0006-2-2','Lớn','Xanh dương',294000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(35,6,'Tím · Nhỏ','PAW-0006-3-1','Nhỏ','Tím',264000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(36,6,'Tím · Lớn','PAW-0006-3-2','Lớn','Tím',294000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(37,7,'Hồng · Nhỏ','PAW-0007-1-1','Nhỏ','Hồng',291000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(38,7,'Hồng · Lớn','PAW-0007-1-2','Lớn','Hồng',321000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(39,7,'Xanh dương · Nhỏ','PAW-0007-2-1','Nhỏ','Xanh dương',291000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(40,7,'Xanh dương · Lớn','PAW-0007-2-2','Lớn','Xanh dương',321000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(41,7,'Tím · Nhỏ','PAW-0007-3-1','Nhỏ','Tím',291000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(42,7,'Tím · Lớn','PAW-0007-3-2','Lớn','Tím',321000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(43,8,'Hồng · Nhỏ','PAW-0008-1-1','Nhỏ','Hồng',318000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(44,8,'Hồng · Lớn','PAW-0008-1-2','Lớn','Hồng',348000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(45,8,'Xanh dương · Nhỏ','PAW-0008-2-1','Nhỏ','Xanh dương',318000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(46,8,'Xanh dương · Lớn','PAW-0008-2-2','Lớn','Xanh dương',348000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(47,8,'Tím · Nhỏ','PAW-0008-3-1','Nhỏ','Tím',318000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(48,8,'Tím · Lớn','PAW-0008-3-2','Lớn','Tím',348000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(49,9,'Be · S','PAW-0009-1-1','S','Be',345000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(50,9,'Be · M','PAW-0009-1-2','M','Be',375000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(51,9,'Đen · S','PAW-0009-2-1','S','Đen',345000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(52,9,'Đen · M','PAW-0009-2-2','M','Đen',375000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(53,9,'Xanh navy · S','PAW-0009-3-1','S','Xanh navy',345000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(54,9,'Xanh navy · M','PAW-0009-3-2','M','Xanh navy',375000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(55,10,'Be · S','PAW-0010-1-1','S','Be',372000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(56,10,'Be · M','PAW-0010-1-2','M','Be',402000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(57,10,'Đen · S','PAW-0010-2-1','S','Đen',372000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(58,10,'Đen · M','PAW-0010-2-2','M','Đen',402000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(59,10,'Xanh navy · S','PAW-0010-3-1','S','Xanh navy',372000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(60,10,'Xanh navy · M','PAW-0010-3-2','M','Xanh navy',402000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(61,11,'Be · S','PAW-0011-1-1','S','Be',399000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(62,11,'Be · M','PAW-0011-1-2','M','Be',429000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(63,11,'Đen · S','PAW-0011-2-1','S','Đen',399000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(64,11,'Đen · M','PAW-0011-2-2','M','Đen',429000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(65,11,'Xanh navy · S','PAW-0011-3-1','S','Xanh navy',399000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(66,11,'Xanh navy · M','PAW-0011-3-2','M','Xanh navy',429000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(67,12,'Be · S','PAW-0012-1-1','S','Be',426000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(68,12,'Be · M','PAW-0012-1-2','M','Be',456000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(69,12,'Đen · S','PAW-0012-2-1','S','Đen',426000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(70,12,'Đen · M','PAW-0012-2-2','M','Đen',456000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(71,12,'Xanh navy · S','PAW-0012-3-1','S','Xanh navy',426000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(72,12,'Xanh navy · M','PAW-0012-3-2','M','Xanh navy',456000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(73,13,'Kem · S','PAW-0013-1-1','S','Kem',453000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(74,13,'Kem · M','PAW-0013-1-2','M','Kem',483000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(75,13,'Xám · S','PAW-0013-2-1','S','Xám',453000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(76,13,'Xám · M','PAW-0013-2-2','M','Xám',483000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(77,13,'Hồng · S','PAW-0013-3-1','S','Hồng',453000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(78,13,'Hồng · M','PAW-0013-3-2','M','Hồng',483000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(79,14,'Kem · S','PAW-0014-1-1','S','Kem',480000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(80,14,'Kem · M','PAW-0014-1-2','M','Kem',510000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(81,14,'Xám · S','PAW-0014-2-1','S','Xám',480000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(82,14,'Xám · M','PAW-0014-2-2','M','Xám',510000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(83,14,'Hồng · S','PAW-0014-3-1','S','Hồng',480000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(84,14,'Hồng · M','PAW-0014-3-2','M','Hồng',510000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(85,15,'Kem · S','PAW-0015-1-1','S','Kem',507000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(86,15,'Kem · M','PAW-0015-1-2','M','Kem',537000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(87,15,'Xám · S','PAW-0015-2-1','S','Xám',507000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(88,15,'Xám · M','PAW-0015-2-2','M','Xám',537000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(89,15,'Hồng · S','PAW-0015-3-1','S','Hồng',507000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(90,15,'Hồng · M','PAW-0015-3-2','M','Hồng',537000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(91,16,'Kem · S','PAW-0016-1-1','S','Kem',534000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(92,16,'Kem · M','PAW-0016-1-2','M','Kem',564000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(93,16,'Xám · S','PAW-0016-2-1','S','Xám',534000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(94,16,'Xám · M','PAW-0016-2-2','M','Xám',564000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(95,16,'Hồng · S','PAW-0016-3-1','S','Hồng',534000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(96,16,'Hồng · M','PAW-0016-3-2','M','Hồng',564000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(97,17,'Trắng · 350 ml','PAW-0017-1-1','350 ml','Trắng',561000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(98,17,'Trắng · 550 ml','PAW-0017-1-2','550 ml','Trắng',591000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(99,17,'Xanh mint · 350 ml','PAW-0017-2-1','350 ml','Xanh mint',561000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(100,17,'Xanh mint · 550 ml','PAW-0017-2-2','550 ml','Xanh mint',591000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(101,17,'Hồng · 350 ml','PAW-0017-3-1','350 ml','Hồng',561000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(102,17,'Hồng · 550 ml','PAW-0017-3-2','550 ml','Hồng',591000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(103,18,'Trắng · 350 ml','PAW-0018-1-1','350 ml','Trắng',588000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(104,18,'Trắng · 550 ml','PAW-0018-1-2','550 ml','Trắng',618000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(105,18,'Xanh mint · 350 ml','PAW-0018-2-1','350 ml','Xanh mint',588000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(106,18,'Xanh mint · 550 ml','PAW-0018-2-2','550 ml','Xanh mint',618000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(107,18,'Hồng · 350 ml','PAW-0018-3-1','350 ml','Hồng',588000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(108,18,'Hồng · 550 ml','PAW-0018-3-2','550 ml','Hồng',618000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(109,19,'Trắng · 350 ml','PAW-0019-1-1','350 ml','Trắng',615000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(110,19,'Trắng · 550 ml','PAW-0019-1-2','550 ml','Trắng',645000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(111,19,'Xanh mint · 350 ml','PAW-0019-2-1','350 ml','Xanh mint',615000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(112,19,'Xanh mint · 550 ml','PAW-0019-2-2','550 ml','Xanh mint',645000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(113,19,'Hồng · 350 ml','PAW-0019-3-1','350 ml','Hồng',615000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(114,19,'Hồng · 550 ml','PAW-0019-3-2','550 ml','Hồng',645000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(115,20,'Trắng · 350 ml','PAW-0020-1-1','350 ml','Trắng',642000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(116,20,'Trắng · 550 ml','PAW-0020-1-2','550 ml','Trắng',672000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(117,20,'Xanh mint · 350 ml','PAW-0020-2-1','350 ml','Xanh mint',642000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(118,20,'Xanh mint · 550 ml','PAW-0020-2-2','550 ml','Xanh mint',672000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(119,20,'Hồng · 350 ml','PAW-0020-3-1','350 ml','Hồng',642000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(120,20,'Hồng · 550 ml','PAW-0020-3-2','550 ml','Hồng',672000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(121,21,'Trắng · Tiêu chuẩn','PAW-0021-1-1','Tiêu chuẩn','Trắng',669000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(122,21,'Trắng · Lớn','PAW-0021-1-2','Lớn','Trắng',699000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(123,21,'Xanh dương · Tiêu chuẩn','PAW-0021-2-1','Tiêu chuẩn','Xanh dương',669000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(124,21,'Xanh dương · Lớn','PAW-0021-2-2','Lớn','Xanh dương',699000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(125,21,'Hồng · Tiêu chuẩn','PAW-0021-3-1','Tiêu chuẩn','Hồng',669000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(126,21,'Hồng · Lớn','PAW-0021-3-2','Lớn','Hồng',699000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(127,22,'Trắng · Tiêu chuẩn','PAW-0022-1-1','Tiêu chuẩn','Trắng',696000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(128,22,'Trắng · Lớn','PAW-0022-1-2','Lớn','Trắng',726000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(129,22,'Xanh dương · Tiêu chuẩn','PAW-0022-2-1','Tiêu chuẩn','Xanh dương',696000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(130,22,'Xanh dương · Lớn','PAW-0022-2-2','Lớn','Xanh dương',726000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(131,22,'Hồng · Tiêu chuẩn','PAW-0022-3-1','Tiêu chuẩn','Hồng',696000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(132,22,'Hồng · Lớn','PAW-0022-3-2','Lớn','Hồng',726000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(133,23,'Trắng · Tiêu chuẩn','PAW-0023-1-1','Tiêu chuẩn','Trắng',723000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(134,23,'Trắng · Lớn','PAW-0023-1-2','Lớn','Trắng',753000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(135,23,'Xanh dương · Tiêu chuẩn','PAW-0023-2-1','Tiêu chuẩn','Xanh dương',723000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(136,23,'Xanh dương · Lớn','PAW-0023-2-2','Lớn','Xanh dương',753000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(137,23,'Hồng · Tiêu chuẩn','PAW-0023-3-1','Tiêu chuẩn','Hồng',723000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(138,23,'Hồng · Lớn','PAW-0023-3-2','Lớn','Hồng',753000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(139,24,'Trắng · Tiêu chuẩn','PAW-0024-1-1','Tiêu chuẩn','Trắng',750000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(140,24,'Trắng · Lớn','PAW-0024-1-2','Lớn','Trắng',780000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(141,24,'Xanh dương · Tiêu chuẩn','PAW-0024-2-1','Tiêu chuẩn','Xanh dương',750000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(142,24,'Xanh dương · Lớn','PAW-0024-2-2','Lớn','Xanh dương',780000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(143,24,'Hồng · Tiêu chuẩn','PAW-0024-3-1','Tiêu chuẩn','Hồng',750000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(144,24,'Hồng · Lớn','PAW-0024-3-2','Lớn','Hồng',780000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(145,25,'Kem · S','PAW-0025-1-1','S','Kem',777000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(146,25,'Kem · M','PAW-0025-1-2','M','Kem',807000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(147,25,'Xanh navy · S','PAW-0025-2-1','S','Xanh navy',777000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(148,25,'Xanh navy · M','PAW-0025-2-2','M','Xanh navy',807000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(149,25,'Đỏ · S','PAW-0025-3-1','S','Đỏ',777000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(150,25,'Đỏ · M','PAW-0025-3-2','M','Đỏ',807000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(151,26,'Kem · S','PAW-0026-1-1','S','Kem',804000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(152,26,'Kem · M','PAW-0026-1-2','M','Kem',834000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(153,26,'Xanh navy · S','PAW-0026-2-1','S','Xanh navy',804000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(154,26,'Xanh navy · M','PAW-0026-2-2','M','Xanh navy',834000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(155,26,'Đỏ · S','PAW-0026-3-1','S','Đỏ',804000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(156,26,'Đỏ · M','PAW-0026-3-2','M','Đỏ',834000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(157,27,'Kem · S','PAW-0027-1-1','S','Kem',831000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(158,27,'Kem · M','PAW-0027-1-2','M','Kem',861000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(159,27,'Xanh navy · S','PAW-0027-2-1','S','Xanh navy',831000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(160,27,'Xanh navy · M','PAW-0027-2-2','M','Xanh navy',861000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(161,27,'Đỏ · S','PAW-0027-3-1','S','Đỏ',831000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(162,27,'Đỏ · M','PAW-0027-3-2','M','Đỏ',861000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(163,28,'Kem · S','PAW-0028-1-1','S','Kem',858000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(164,28,'Kem · M','PAW-0028-1-2','M','Kem',888000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(165,28,'Xanh navy · S','PAW-0028-2-1','S','Xanh navy',858000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(166,28,'Xanh navy · M','PAW-0028-2-2','M','Xanh navy',888000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(167,28,'Đỏ · S','PAW-0028-3-1','S','Đỏ',858000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(168,28,'Đỏ · M','PAW-0028-3-2','M','Đỏ',888000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(169,29,'Be · Tiêu chuẩn','PAW-0029-1-1','Tiêu chuẩn','Be',885000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(170,29,'Be · Lớn','PAW-0029-1-2','Lớn','Be',915000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(171,29,'Đen · Tiêu chuẩn','PAW-0029-2-1','Tiêu chuẩn','Đen',885000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(172,29,'Đen · Lớn','PAW-0029-2-2','Lớn','Đen',915000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(173,29,'Xanh rêu · Tiêu chuẩn','PAW-0029-3-1','Tiêu chuẩn','Xanh rêu',885000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(174,29,'Xanh rêu · Lớn','PAW-0029-3-2','Lớn','Xanh rêu',915000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(175,30,'Be · Tiêu chuẩn','PAW-0030-1-1','Tiêu chuẩn','Be',912000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(176,30,'Be · Lớn','PAW-0030-1-2','Lớn','Be',942000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(177,30,'Đen · Tiêu chuẩn','PAW-0030-2-1','Tiêu chuẩn','Đen',912000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(178,30,'Đen · Lớn','PAW-0030-2-2','Lớn','Đen',942000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(179,30,'Xanh rêu · Tiêu chuẩn','PAW-0030-3-1','Tiêu chuẩn','Xanh rêu',912000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(180,30,'Xanh rêu · Lớn','PAW-0030-3-2','Lớn','Xanh rêu',942000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(181,31,'Be · Tiêu chuẩn','PAW-0031-1-1','Tiêu chuẩn','Be',939000,3,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(182,31,'Be · Lớn','PAW-0031-1-2','Lớn','Be',969000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(183,31,'Đen · Tiêu chuẩn','PAW-0031-2-1','Tiêu chuẩn','Đen',939000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(184,31,'Đen · Lớn','PAW-0031-2-2','Lớn','Đen',969000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(185,31,'Xanh rêu · Tiêu chuẩn','PAW-0031-3-1','Tiêu chuẩn','Xanh rêu',939000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(186,31,'Xanh rêu · Lớn','PAW-0031-3-2','Lớn','Xanh rêu',969000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(187,32,'Be · Tiêu chuẩn','PAW-0032-1-1','Tiêu chuẩn','Be',966000,4,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(188,32,'Be · Lớn','PAW-0032-1-2','Lớn','Be',996000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(189,32,'Đen · Tiêu chuẩn','PAW-0032-2-1','Tiêu chuẩn','Đen',966000,5,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(190,32,'Đen · Lớn','PAW-0032-2-2','Lớn','Đen',996000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(191,32,'Xanh rêu · Tiêu chuẩn','PAW-0032-3-1','Tiêu chuẩn','Xanh rêu',966000,6,1,'2026-08-31 06:49:57','2026-08-31 06:49:57'),(192,32,'Xanh rêu · Lớn','PAW-0032-3-2','Lớn','Xanh rêu',996000,7,1,'2026-08-31 06:49:57','2026-08-31 06:49:57');
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
INSERT INTO `products` VALUES (1,1,'Bóng cao su Pounce','bong-cao-su-pounce','PAW-0001','dog','Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.','Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',129000,209000,27,'/media/products/pawora-clean/pawora-product-0001.png',5.0,1,1,'active','2026-08-31 06:49:57','2026-08-31 06:49:58',NULL),(2,1,'Dây thừng Tug & Joy','day-thung-tug-joy','PAW-0002','dog','Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.','Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',156000,NULL,33,'/media/products/pawora-clean/pawora-product-0002.png',4.0,1,1,'active','2026-08-31 06:49:57','2026-08-31 06:49:58',NULL),(3,1,'Đĩa bay Flexi Disc','dia-bay-flexi-disc','PAW-0003','dog','Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.','Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',183000,NULL,34,'/media/products/pawora-clean/pawora-product-0003.png',5.0,1,1,'active','2026-08-31 06:49:57','2026-08-31 06:49:58',NULL),(4,1,'Đồ chơi phát tiếng Nibble','do-choi-phat-tieng-nibble','PAW-0004','dog','Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.','Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',210000,290000,30,'/media/products/pawora-clean/pawora-product-0004.png',4.0,1,1,'active','2026-08-31 06:49:57','2026-08-31 06:49:58',NULL),(5,2,'Cần câu lông vũ Miso','can-cau-long-vu-miso','PAW-0005','cat','Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.','Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',237000,NULL,26,'/media/products/pawora-clean/pawora-product-0005.png',4.5,2,1,'active','2026-08-31 06:49:57','2026-08-31 06:49:58',NULL),(6,2,'Bóng len Moon Ball','bong-len-moon-ball','PAW-0006','cat','Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.','Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',264000,NULL,27,'/media/products/pawora-clean/pawora-product-0006.png',4.0,2,1,'active','2026-08-31 06:49:57','2026-08-31 06:49:58',NULL),(7,2,'Bàn cào móng Cloud','ban-cao-mong-cloud','PAW-0007','cat','Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.','Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',291000,371000,33,'/media/products/pawora-clean/pawora-product-0007.png',4.0,2,1,'active','2026-08-31 06:49:57','2026-08-31 06:49:58',NULL),(8,2,'Đường hầm Hide & Seek','duong-ham-hide-seek','PAW-0008','cat','Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.','Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',318000,NULL,34,'/media/products/pawora-clean/pawora-product-0008.png',5.0,1,1,'active','2026-08-31 06:49:57','2026-08-31 06:49:58',NULL),(9,3,'Vòng cổ AirTag Linen','vong-co-airtag-linen','PAW-0009','all','Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.','Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',345000,NULL,30,'/media/products/pawora-clean/pawora-product-0009.jpg',4.0,2,0,'active','2026-08-31 06:49:57','2026-08-31 06:49:58',NULL),(10,3,'Dây dắt Urban Walk','day-dat-urban-walk','PAW-0010','all','Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.','Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',372000,452000,26,'/media/products/pawora-clean/pawora-product-0010.jpg',4.0,1,0,'active','2026-08-31 06:49:57','2026-08-31 06:49:58',NULL),(11,3,'Yếm ngực Comfort Pro','yem-nguc-comfort-pro','PAW-0011','all','Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.','Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',399000,NULL,27,'/media/products/pawora-clean/pawora-product-0011.jpg',5.0,1,0,'active','2026-08-31 06:49:57','2026-08-31 06:49:58',NULL),(12,3,'Bộ dây dắt Weekend','bo-day-dat-weekend','PAW-0012','all','Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.','Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',426000,NULL,33,'/media/products/pawora-clean/pawora-product-0012.jpg',5.0,1,0,'active','2026-08-31 06:49:57','2026-08-31 06:49:58',NULL),(13,4,'Giường ngủ Cocoon','giuong-ngu-cocoon','PAW-0013','all','Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.','Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',453000,533000,34,'/media/products/pawora-clean/pawora-product-0013.jpg',4.0,1,0,'active','2026-08-31 06:49:57','2026-08-31 06:49:58',NULL),(14,4,'Nệm mây Soft Nest','nem-may-soft-nest','PAW-0014','all','Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.','Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',480000,NULL,30,'/media/products/pawora-clean/pawora-product-0014.jpg',0.0,0,0,'active','2026-08-31 06:49:57','2026-08-31 06:49:58',NULL),(15,4,'Chăn cotton Calm','chan-cotton-calm','PAW-0015','all','Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.','Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',507000,NULL,26,'/media/products/pawora-clean/pawora-product-0015.jpg',5.0,1,0,'active','2026-08-31 06:49:57','2026-08-31 06:49:58',NULL),(16,4,'Nhà vải Hideaway','nha-vai-hideaway','PAW-0016','all','Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.','Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',534000,614000,27,'/media/products/pawora-clean/pawora-product-0016.jpg',0.0,0,0,'active','2026-08-31 06:49:57','2026-08-31 06:49:58',NULL),(17,5,'Bát sứ Slow Dining','bat-su-slow-dining','PAW-0017','all','Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.','Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',561000,NULL,33,'/media/products/pawora-clean/pawora-product-0017.jpg',0.0,0,0,'active','2026-08-31 06:49:57','2026-08-31 06:49:58',NULL),(18,5,'Bình nước Travel Sip','binh-nuoc-travel-sip','PAW-0018','all','Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.','Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',588000,NULL,34,'/media/products/pawora-clean/pawora-product-0018.jpg',0.0,0,0,'active','2026-08-31 06:49:57','2026-08-31 06:49:58',NULL),(19,5,'Bát đôi Oak Stand','bat-doi-oak-stand','PAW-0019','all','Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.','Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',615000,695000,30,'/media/products/pawora-clean/pawora-product-0019.jpg',0.0,0,0,'active','2026-08-31 06:49:57','2026-08-31 06:49:58',NULL),(20,5,'Máy nước Fresh Flow','may-nuoc-fresh-flow','PAW-0020','all','Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.','Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',642000,NULL,26,'/media/products/pawora-clean/pawora-product-0020.jpg',0.0,0,0,'active','2026-08-31 06:49:57','2026-08-31 06:49:58',NULL),(21,6,'Lược chải Easy Coat','luoc-chai-easy-coat','PAW-0021','all','Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.','Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',669000,NULL,27,'/media/products/pawora-clean/pawora-product-0021.jpg',0.0,0,0,'active','2026-08-31 06:49:57','2026-08-31 06:49:58',NULL),(22,6,'Kềm cắt móng Safe Trim','kem-cat-mong-safe-trim','PAW-0022','all','Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.','Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',696000,776000,33,'/media/products/pawora-clean/pawora-product-0022.jpg',0.0,0,0,'active','2026-08-31 06:49:57','2026-08-31 06:49:58',NULL),(23,6,'Găng tay Gentle Groom','gang-tay-gentle-groom','PAW-0023','all','Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.','Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',723000,NULL,34,'/media/products/pawora-clean/pawora-product-0023.jpg',0.0,0,0,'active','2026-08-31 06:49:57','2026-08-31 06:49:58',NULL),(24,6,'Khăn tắm Quick Dry','khan-tam-quick-dry','PAW-0024','all','Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.','Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',750000,NULL,30,'/media/products/pawora-clean/pawora-product-0024.jpg',0.0,0,0,'active','2026-08-31 06:49:57','2026-08-31 06:49:58',NULL),(25,7,'Áo thun Everyday','ao-thun-everyday','PAW-0025','all','Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.','Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',777000,857000,26,'/media/products/pawora-clean/pawora-product-0025.jpg',0.0,0,0,'active','2026-08-31 06:49:57','2026-08-31 06:49:58',NULL),(26,7,'Áo mưa City Walk','ao-mua-city-walk','PAW-0026','all','Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.','Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',804000,NULL,27,'/media/products/pawora-clean/pawora-product-0026.jpg',0.0,0,0,'active','2026-08-31 06:49:57','2026-08-31 06:49:58',NULL),(27,7,'Khăn choàng Pawora','khan-choang-pawora','PAW-0027','all','Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.','Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',831000,NULL,33,'/media/products/pawora-clean/pawora-product-0027.jpg',0.0,0,0,'active','2026-08-31 06:49:57','2026-08-31 06:49:58',NULL),(28,7,'Áo len Warm Hug','ao-len-warm-hug','PAW-0028','all','Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.','Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',858000,938000,34,'/media/products/pawora-clean/pawora-product-0028.jpg',0.0,0,0,'active','2026-08-31 06:49:57','2026-08-31 06:49:58',NULL),(29,8,'Túi vận chuyển Go Together','tui-van-chuyen-go-together','PAW-0029','all','Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.','Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',885000,NULL,30,'/media/products/pawora-clean/pawora-product-0029.jpg',0.0,0,0,'active','2026-08-31 06:49:57','2026-08-31 06:49:58',NULL),(30,8,'Đệm xe Road Trip','dem-xe-road-trip','PAW-0030','all','Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.','Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',912000,NULL,26,'/media/products/pawora-clean/pawora-product-0030.jpg',0.0,0,0,'active','2026-08-31 06:49:57','2026-08-31 06:49:58',NULL),(31,8,'Túi đựng snack Walkie','tui-dung-snack-walkie','PAW-0031','all','Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.','Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',939000,1019000,27,'/media/products/pawora-clean/pawora-product-0031.jpg',0.0,0,0,'active','2026-08-31 06:49:57','2026-08-31 06:49:58',NULL),(32,8,'Bình nước Pocket Sip','binh-nuoc-pocket-sip','PAW-0032','all','Thiết kế tinh gọn, chất liệu an toàn và thoải mái cho bé trong nhịp sống hằng ngày.','Pawora chọn từng chi tiết với ưu tiên an toàn, độ bền và trải nghiệm sử dụng. Sản phẩm dễ vệ sinh, phù hợp dùng hằng ngày và có hướng dẫn bảo quản rõ ràng.',966000,NULL,33,'/media/products/pawora-clean/pawora-product-0032.jpg',0.0,0,0,'active','2026-08-31 06:49:57','2026-08-31 06:49:58',NULL);
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
INSERT INTO `users` VALUES (1,'Pawora Admin','admin@pawora.vn','0909000001',NULL,NULL,NULL,NULL,'$2y$10$RATlTN9vjFJcWxGPSbzlAehYeZSxAJn2g1cviVQX/JxIVVou/GZWW','admin',1,NULL,NULL,'2026-08-31 06:49:57','2026-08-31 06:49:57',NULL,NULL,NULL),(2,'Nguyễn An','khach@pawora.vn','0909000002','Thành phố Hà Nội','Phường Tây Tựu','Số 18, ngõ 5 Ngọa Long',NULL,'$2y$10$f5T6nzKdZkPfsOGi2jr2oOr8LG7j9mSXHc0fvAdjxdsCV.DEl3w22','customer',1,NULL,NULL,'2026-08-31 06:49:57','2026-08-31 06:49:57',NULL,NULL,NULL),(3,'Trần Minh Anh','minhanh.demo@pawora.vn','0911000101','Thành phố Hà Nội','Phường Tây Tựu','18, đường Pawora',NULL,'$2y$10$q0ead3C.ZalLkZHYTXC8MOAfDsV36VZqR75eLMIhhDcQ3e5c0u0p2','customer',1,NULL,NULL,'2026-08-31 06:49:57','2026-08-31 06:49:57',NULL,NULL,NULL),(4,'Lê Hoàng Nam','hoangnam.demo@pawora.vn','0911000102','Thành phố Hồ Chí Minh','Phường Bến Thành','19, đường Pawora',NULL,'$2y$10$weFYHfDzJ1e.phpiQs/XeuZREX6P3Q/7Q.cEXmPlqAT0zMkBSuHEO','customer',1,NULL,NULL,'2026-08-31 06:49:57','2026-08-31 06:49:57',NULL,NULL,NULL),(5,'Phạm Thu Trang','thutrang.demo@pawora.vn','0911000103','Thành phố Hà Nội','Phường Tây Tựu','20, đường Pawora',NULL,'$2y$10$UOyd7YEYNqXoy3xUjlo4MuErVcQFndkiEl3IdQFgb0odoYjpoe1w.','customer',1,NULL,NULL,'2026-08-31 06:49:57','2026-08-31 06:49:57',NULL,NULL,NULL),(6,'Nguyễn Đức Huy','duchuy.demo@pawora.vn','0911000104','Thành phố Hồ Chí Minh','Phường Bến Thành','21, đường Pawora',NULL,'$2y$10$s2oDHLtxn0AzX2zJYozHWeOmPKlmiRoU8S.0C8ySGbu8CcfF0EOYm','customer',1,NULL,NULL,'2026-08-31 06:49:58','2026-08-31 06:49:58',NULL,NULL,NULL),(7,'Đỗ Khánh Linh','khanhlinh.demo@pawora.vn','0911000105','Thành phố Hà Nội','Phường Tây Tựu','22, đường Pawora',NULL,'$2y$10$DXxhuRy2DOx7s6r0HmUeG.m3uqeEYSHUVVbF1QyF1PBKAovhy2tQy','customer',1,NULL,NULL,'2026-08-31 06:49:58','2026-08-31 06:49:58',NULL,NULL,NULL),(8,'Vũ Quang Minh','quangminh.demo@pawora.vn','0911000106','Thành phố Hồ Chí Minh','Phường Bến Thành','23, đường Pawora',NULL,'$2y$10$juc5yGQc71A2qWnhR9kwiupwewc1bJObx8ksM8nv1BDv98MHEtz12','customer',1,NULL,NULL,'2026-08-31 06:49:58','2026-08-31 06:49:58',NULL,NULL,NULL),(9,'Bùi Ngọc Mai','ngocmai.demo@pawora.vn','0911000107','Thành phố Hà Nội','Phường Tây Tựu','24, đường Pawora',NULL,'$2y$10$MVug/xePsGDczrFoGNkkJu1oWH9SmmTTBvULxFrqAmA.VMg/GyJda','customer',1,NULL,NULL,'2026-08-31 06:49:58','2026-08-31 06:49:58',NULL,NULL,NULL),(10,'Hoàng Gia Bảo','giabao.demo@pawora.vn','0911000108','Thành phố Hồ Chí Minh','Phường Bến Thành','25, đường Pawora',NULL,'$2y$10$MvXkYgLlWUMx0h9A5aWaO.uQrRvzs22RIpKypS5FdTDADXYKOdVsi','customer',1,NULL,NULL,'2026-08-31 06:49:58','2026-08-31 06:49:58',NULL,NULL,NULL);
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

