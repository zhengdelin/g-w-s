-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1:3306
-- 產生時間： 2021-12-22 11:32:33
-- 伺服器版本： 8.0.21
-- PHP 版本： 7.3.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `genshin`
--

-- --------------------------------------------------------

--
-- 資料表結構 `migrations`
--
-- 建立時間： 2021-12-22 11:32:16
-- 最後更新： 2021-12-22 11:32:16
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 資料表新增資料前，先清除舊資料 `migrations`
--

TRUNCATE TABLE `migrations`;
--
-- 傾印資料表的資料 `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2021_12_12_041101_create_pool_infos_table', 1),
(2, '2021_12_12_042134_create_five_stds_table', 1),
(3, '2021_12_12_042342_create_four_stds_table', 1),
(4, '2021_12_12_042912_create_five_wp_ups_table', 1),
(5, '2021_12_12_044316_create_four_cr_ups_table', 1),
(6, '2021_12_12_044536_create_four_wp_ups_table', 1),
(7, '2021_12_12_044636_create_pool_imgs_table', 1),
(8, '2021_12_12_052004_create_cr_wp_infos_table', 1),
(9, '2021_12_13_085159_create_videos_table', 1),
(10, '2021_12_13_094029_create_three_stds_table', 1),
(11, '2021_12_22_112711_create_detail_box_pictures_table', 1);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
