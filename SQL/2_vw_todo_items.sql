-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 25, 2019 at 02:16 AM
-- Server version: 10.4.10-MariaDB
-- PHP Version: 7.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_todo`
--

-- --------------------------------------------------------

--
-- Structure for view `vw_todo_items`
--

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_todo_items`  AS  select `tbl_todo_items`.`todo_item_id` AS `todo_item_id`,`tbl_todo_items`.`todo_item_name` AS `todo_item_name`,`tbl_todo_items`.`todo_item_description` AS `todo_item_description`,`tbl_todo_items`.`todo_item_due_date` AS `todo_item_due_date`,`tbl_todo_items`.`todo_item_is_completed` AS `todo_item_is_completed`,`tbl_todo_items`.`todo_is_deleted` AS `todo_is_deleted` from `tbl_todo_items` where `tbl_todo_items`.`todo_is_deleted` = 0 ;

--
-- VIEW  `vw_todo_items`
-- Data: None
--

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
