-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for heart
CREATE DATABASE IF NOT EXISTS `heart` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `heart`;

-- Dumping structure for event heart.daily_donor_and_food_tally
DELIMITER //
CREATE EVENT `daily_donor_and_food_tally` ON SCHEDULE EVERY 1 DAY STARTS '2025-10-15 01:00:00' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    
    -- คำนวณวันที่เมื่อวานนี้
    SET @yesterday = CURDATE() - INTERVAL 1 DAY;

    INSERT INTO daily_donor_count (date_counted, donor_count, food_amout_sum)
    SELECT
        @yesterday, -- 1. วันที่นับ (เมื่อวาน)
        COUNT(DISTINCT user_id),
        COALESCE(SUM(amout), 0) 
    FROM
        donations
    WHERE

        DATE(created_at) = @yesterday 
    
    ON DUPLICATE KEY UPDATE 
        donor_count = VALUES(donor_count),
        food_amout_sum = VALUES(food_amout_sum);
        
END//
DELIMITER ;

-- Dumping structure for table heart.daily_donor_count
CREATE TABLE IF NOT EXISTS `daily_donor_count` (
  `date_counted` date NOT NULL,
  `donor_count` int NOT NULL,
  `food_amout_sum` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`date_counted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table heart.daily_donor_count: ~1 rows (approximately)
INSERT INTO `daily_donor_count` (`date_counted`, `donor_count`, `food_amout_sum`) VALUES
	('2025-10-19', 10, 200);

-- Dumping structure for table heart.donations
CREATE TABLE IF NOT EXISTS `donations` (
  `donations_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `project_id` int DEFAULT NULL,
  `food_type` int DEFAULT NULL,
  `food_name` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `amount` int DEFAULT NULL,
  `MFG` date DEFAULT NULL,
  `EXP` date DEFAULT NULL,
  `food_img` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'ยังไม่จัดส่ง',
  `created_at` timestamp NULL DEFAULT (now()),
  PRIMARY KEY (`donations_id`) USING BTREE,
  KEY `User_Donations` (`user_id`),
  KEY `Project_Donation` (`project_id`),
  KEY `Food_Type_Donations` (`food_type`),
  CONSTRAINT `Food_Type_Donations` FOREIGN KEY (`food_type`) REFERENCES `food_type` (`food_type_id`),
  CONSTRAINT `Project_Donation` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`),
  CONSTRAINT `User_Donations` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table heart.donations: ~14 rows (approximately)
INSERT INTO `donations` (`donations_id`, `user_id`, `project_id`, `food_type`, `food_name`, `amount`, `MFG`, `EXP`, `food_img`, `status`, `created_at`) VALUES
	(1, 2, 1, 1, 'นมจืด', 50, '2025-09-15', '2025-09-14', NULL, 'ยังไม่จัดส่ง', '2025-10-14 12:14:41'),
	(2, 1, 1, 3, 'แอปเปิ้ล', 30, '2025-08-31', '2025-09-16', NULL, 'ยังไม่จัดส่ง', '2025-10-14 12:14:41'),
	(3, 3, 2, 12, 'ขนมปัง', 60, '2025-09-15', '2025-09-14', NULL, 'จัดส่งแล้ว', '2025-10-14 12:14:41'),
	(4, 4, 2, 10, 'ขนม', 20, '2025-08-15', '2026-10-15', NULL, 'จัดส่งแล้ว', '2025-10-14 12:14:41'),
	(5, 6, 3, 7, 'ปลา', 40, '2025-09-22', '2025-09-25', NULL, 'จัดส่งแล้ว', '2025-10-14 12:14:41'),
	(21, NULL, 3, 8, 'ไข่', 100, '2025-09-27', '2025-09-29', NULL, 'ยังไม่จัดส่ง', '2025-10-14 12:14:41'),
	(22, NULL, 3, 8, 'ไข่ไก่', 100, '2025-09-18', '2025-09-30', 'uploads/68d91511aed98_Screenshot (1).png', 'ยังไม่จัดส่ง', '2025-10-14 12:14:41'),
	(23, NULL, 1, 7, 'ปลา', 50, '2025-10-13', '2025-10-15', NULL, 'จัดส่งแล้ว', '2025-10-14 12:14:41'),
	(24, NULL, 1, 11, 'ข้าวมันไก่', 30, '2025-10-14', '2025-10-15', NULL, 'ยังไม่จัดส่ง', '2025-10-14 12:55:43'),
	(25, NULL, 1, 11, 'ข้าว', 100, '2025-10-16', '2025-10-17', NULL, 'ยังไม่จัดส่ง', '2025-10-16 07:15:26'),
	(26, NULL, 1, 7, 'asdf', 100, '2025-10-16', '2025-10-17', NULL, 'ยังไม่จัดส่ง', '2025-10-16 07:26:05'),
	(27, NULL, 1, 9, 'asfohaui', 100, '2025-10-16', '2025-10-14', NULL, 'ยังไม่จัดส่ง', '2025-10-16 08:24:13'),
	(28, NULL, 1, 8, 'w-j', 100, '2025-10-24', '2025-10-20', 'uploads/68f110dc4eb6d_licensed-image.jpg', 'จัดส่งแล้ว', '2025-10-16 15:35:56'),
	(29, NULL, 1, 6, 'drhrr', 100, '2025-10-14', '2025-10-14', NULL, 'ยังไม่จัดส่ง', '2025-10-19 08:27:28');

-- Dumping structure for table heart.food_type
CREATE TABLE IF NOT EXISTS `food_type` (
  `food_type_id` int NOT NULL AUTO_INCREMENT,
  `Description` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`food_type_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table heart.food_type: ~13 rows (approximately)
INSERT INTO `food_type` (`food_type_id`, `Description`) VALUES
	(1, 'นม'),
	(2, 'อาหารแช่แข็ง'),
	(3, 'ผลไม้และผัก'),
	(4, 'โกโก้และช็อกโกแลต'),
	(5, 'เนื้อสัตว์'),
	(6, 'ธัญชาติ และ ธัญพืช'),
	(7, 'สัตว์น้ำ'),
	(8, 'ไข่'),
	(9, 'เครื่องดื่ม'),
	(10, 'ขนมขบเคี้ยว'),
	(11, 'อาหารเตรียมสำเร็จ'),
	(12, 'ขนมปัง'),
	(13, 'อื่นๆ');

-- Dumping structure for table heart.projects
CREATE TABLE IF NOT EXISTS `projects` (
  `project_id` int NOT NULL AUTO_INCREMENT,
  `owner_id` int DEFAULT NULL,
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `img` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `goal` int DEFAULT NULL,
  `start_date` timestamp NULL DEFAULT (now()),
  `end_date` timestamp NULL DEFAULT NULL,
  `address` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`project_id`) USING BTREE,
  KEY `User_Projects` (`owner_id`),
  CONSTRAINT `User_Projects` FOREIGN KEY (`owner_id`) REFERENCES `user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table heart.projects: ~4 rows (approximately)
INSERT INTO `projects` (`project_id`, `owner_id`, `title`, `img`, `description`, `goal`, `start_date`, `end_date`, `address`, `phone`, `email`, `status`) VALUES
	(1, 3, 'ส่งต่อมื้ออาหารให้กลุ่มผู้เปราะบาง SOS', 'uploads/projects/1.png', 'โครงการ Food Rescue Program มีเป้าหมายเพื่อลดปัญหาอาหารส่วนเกินและความไม่มั่นคงทางอาหารในสังคมไทย โดยการนำอาหารส่วนเกินจากซูเปอร์มาร์เก็ต ร้านอาหาร และโรงแรม ที่ยังรับประทานได้ มาแจกจ่ายให้กับชุมชนที่ขาดแคลน ผ่านเครือข่ายองค์กรการกุศลและธนาคารอาหาร วิธีการนี้ช่วยลดขยะอาหาร ลดผลกระทบต่อสิ่งแวดล้อม และเพิ่มโอกาสในการเข้าถึงอาหารที่มีคุณค่าทางโภชนาการให้กับผู้ที่ต้องการ', 500, '2025-09-11 17:32:53', '2025-12-11 17:32:54', '77 ถ. หลานหลวง แขวงวัดโสมนัส เขตป้อมปราบศัตรูพ่าย กรุงเทพมหานคร 10100', '062 675 0004', 'info@scholarsofsustenance.org', 'open'),
	(2, 3, 'โครงการอาหารกลางวัน', 'uploads/projects/2.png', 'ผู้ใหญ่ใจดีสามารถแบ่งปันความอิ่มและความสุขเป็นทุนหรือประกอบอาหารเพื่อสนับสนุนโครงการอาหารกลางวัน ในวันที่มูลนิธิเพื่อเด็กพิการจัดกิจกรรม “ศูนย์บริการคนพิการทั่วไป ” ทุกวันจันทร์ – วันอังคาร – พฤหัสบดี หรือกิจกรรมอบรมทักษะความรู้ต่างๆ', 50, '2025-07-11 17:34:21', '2025-10-16 17:00:00', '546 ซอยลาดพร้าว 47 ถนนลาดพร้าว แขวงสะพานสอง เขตวังทองหลาง กรุงเทพฯ 10310', '02-539-9958', 'fcdthailand@yahoo.com', 'open'),
	(3, 6, 'โครงการ FOOD FOR GOOD', 'uploads/projects/3.png', 'โครงการ FOOD FOR GOOD ก่อตั้งขึ้นในปี พ.ศ. 2557 ดำเนินงานภายใต้มูลนิธิยุวพัฒน์ มีวัตถุประสงค์เพื่อช่วยเหลือให้เด็กได้รับความสมดุลด้านโภชนาการ สร้างรากฐานการเติบโตที่แข็งแรงให้กับอนาคตของประเทศ', 60, '2025-09-28 08:55:49', '2025-09-28 08:55:50', 'เลขที่ 1 ซ.พรีเมียร์ 2 ถ.ศรีนครินทร์ แขวงหนองบอน เขตประเวศ กรุงเทพฯ 10250', '065 520 9141', 'info@foodforgood.or.th', 'open'),
	(35, 3, 'asdasdadas', 'uploads/projects/proj_20251019_104742_c741b715.png', 'ิีัอดีัอดฟีได', 5000, NULL, '2025-10-24 16:59:59', 'ต.ห้วยข้าวก่ำ อ.จุน จ.พะเยา 56150', '0654485167', 'datazxc@gmail.com', 'pending');

-- Dumping structure for procedure heart.Refresh_Sum_Results
DELIMITER //
CREATE PROCEDURE `Refresh_Sum_Results`()
BEGIN
    DELETE FROM sum_results;

    INSERT INTO sum_results (
        project_sum,
        food_sum,
        donate_sum,
        not_shipped_sum,
        shipped_sum
    )
    SELECT
        (SELECT COUNT(project_id) FROM projects) AS project_count,
        (SELECT COALESCE(SUM(amount), 0) FROM donations) AS total_food_amount,
        (SELECT COUNT(*) FROM donations) AS donate_count,                -- เติมค่าอันที่ 3
        (SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status = 'ยังไม่จัดส่ง') AS total_not_shipped,
        (SELECT COALESCE(SUM(amount), 0) FROM donations WHERE status = 'จัดส่งแล้ว') AS total_shipped;
END//
DELIMITER ;

-- Dumping structure for table heart.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `role_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`role_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table heart.roles: ~3 rows (approximately)
INSERT INTO `roles` (`role_id`, `name`) VALUES
	(1, 'user'),
	(2, 'owner'),
	(3, 'admin\r\n');

-- Dumping structure for table heart.sum_results
CREATE TABLE IF NOT EXISTS `sum_results` (
  `project_sum` int DEFAULT NULL,
  `food_sum` int DEFAULT NULL,
  `donate_sum` int DEFAULT NULL,
  `not_shipped_sum` int unsigned DEFAULT NULL,
  `shipped_sum` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table heart.sum_results: ~1 rows (approximately)
INSERT INTO `sum_results` (`project_sum`, `food_sum`, `donate_sum`, `not_shipped_sum`, `shipped_sum`) VALUES
	(4, 980, 14, 710, 270);

-- Dumping structure for table heart.user
CREATE TABLE IF NOT EXISTS `user` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `role_id` int DEFAULT NULL,
  `email` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `name` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`user_id`) USING BTREE,
  KEY `Role_User` (`role_id`),
  CONSTRAINT `Role_User` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table heart.user: ~6 rows (approximately)
INSERT INTO `user` (`user_id`, `role_id`, `email`, `password`, `name`) VALUES
	(1, 1, 'henry@heart.com', 'password1', 'henry'),
	(2, 1, 'hans@heart.com', 'password2', 'hans'),
	(3, 2, 'jim@heart.com', 'password3', 'jim'),
	(4, 2, 'alice@heart.com', 'password4', 'alice'),
	(5, 3, 'data@heart.com', 'password5', 'data'),
	(6, 3, 'fail@heart.com', 'password6', 'fail');

-- Dumping structure for trigger heart.trg_donations_after_delete
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trg_donations_after_delete` AFTER DELETE ON `donations` FOR EACH ROW BEGIN
    CALL Refresh_Sum_Results();
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger heart.trg_donations_after_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trg_donations_after_insert` AFTER INSERT ON `donations` FOR EACH ROW BEGIN
    CALL Refresh_Sum_Results();
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger heart.trg_donations_after_update
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trg_donations_after_update` AFTER UPDATE ON `donations` FOR EACH ROW BEGIN

    IF OLD.amount <> NEW.amount OR OLD.status <> NEW.status THEN
        CALL Refresh_Sum_Results();
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger heart.trg_food_type_after_change
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trg_food_type_after_change` AFTER INSERT ON `food_type` FOR EACH ROW BEGIN
    CALL Refresh_Sum_Results();
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger heart.trg_projects_after_change
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trg_projects_after_change` AFTER INSERT ON `projects` FOR EACH ROW BEGIN
    CALL Refresh_Sum_Results();
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
