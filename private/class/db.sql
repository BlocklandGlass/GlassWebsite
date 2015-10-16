-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 15, 2015 at 08:24 PM
-- Server version: 5.5.43-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `blocklandGlass`
--
CREATE DATABASE IF NOT EXISTS `blocklandGlass` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `blocklandGlass`;

-- --------------------------------------------------------

--
-- Table structure for table `addon_addons`
--

CREATE TABLE IF NOT EXISTS `addon_addons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `board` int(11) NOT NULL,
  `author` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `filename` text NOT NULL,
  `description` text NOT NULL,
  `screenshots` int(11) NOT NULL DEFAULT '0',
  `approvalInfo` text NOT NULL,
  `updaterInfo` text NOT NULL,
  `ratingInfo` text NOT NULL,
  `dependancies` text NOT NULL,
  `bargain` tinyint(1) NOT NULL DEFAULT '0',
  `danger` tinyint(1) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL,
  `file_stable` int(11) DEFAULT NULL,
  `file_testing` int(11) DEFAULT NULL,
  `file_dev` int(11) DEFAULT NULL,
  `downloads_web` int(11) NOT NULL,
  `downloads_ingame` int(11) NOT NULL,
  `downloads_update` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=34 ;

-- --------------------------------------------------------

--
-- Table structure for table `addon_boards`
--

CREATE TABLE IF NOT EXISTS `addon_boards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `file_header` varchar(10) NOT NULL,
  `icon` varchar(24) NOT NULL,
  `subcategory` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=16 ;

-- --------------------------------------------------------

--
-- Table structure for table `addon_comments`
--

CREATE TABLE IF NOT EXISTS `addon_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `comment` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=20 ;

-- --------------------------------------------------------

--
-- Table structure for table `addon_files`
--

CREATE TABLE IF NOT EXISTS `addon_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hash` varchar(256) NOT NULL,
  `malicious` int(11) NOT NULL,
  `author` int(6) NOT NULL,
  `problems` mediumtext,
  `client` tinyint(1) NOT NULL DEFAULT '0',
  `server` tinyint(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=164 ;

-- --------------------------------------------------------

--
-- Table structure for table `addon_rtb`
--

CREATE TABLE IF NOT EXISTS `addon_rtb` (
  `rtbId` int(11) NOT NULL,
  `glassId` int(11) DEFAULT NULL,
  `filename` varchar(30) NOT NULL,
  `owner` int(11) DEFAULT NULL,
  `author` varchar(32) NOT NULL,
  `downloads` int(11) NOT NULL,
  `importData` text NOT NULL,
  UNIQUE KEY `rtbId` (`rtbId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `addon_updates`
--

CREATE TABLE IF NOT EXISTS `addon_updates` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `aid` int(11) NOT NULL,
  `version` varchar(64) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `changelog` longtext NOT NULL,
  `branch` varchar(10) NOT NULL,
  `restart` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=51 ;

-- --------------------------------------------------------

--
-- Table structure for table `ingame_sessions`
--

CREATE TABLE IF NOT EXISTS `ingame_sessions` (
  `blid` int(6) NOT NULL,
  `sessionid` varchar(32) NOT NULL,
  `start` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastactive` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `version` text NOT NULL,
  UNIQUE KEY `sessionid` (`sessionid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE IF NOT EXISTS `notifications` (
  `image` varchar(16) NOT NULL,
  `type` text NOT NULL,
  `data` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`timestamp`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL,
  `blid` int(6) NOT NULL,
  `password` varchar(64) NOT NULL,
  `salt` varchar(10) NOT NULL,
  `session_last_active` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `verified` tinyint(1) NOT NULL DEFAULT '0',
  `groups` mediumtext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_reviewer_app`
--

CREATE TABLE IF NOT EXISTS `user_reviewer_app` (
  `uid` int(11) NOT NULL,
  `submitted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `response` text NOT NULL,
  UNIQUE KEY `submitted` (`submitted`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
