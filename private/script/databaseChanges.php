<?php
require_once dirname(__DIR__) . '/class/DatabaseManager.php';
$database = new DatabaseManager();
$database->query("ALTER TABLE `addon_boards` DROP video");
$database->query("ALTER TABLE `addon_boards` ADD icon VARCHAR(24) NOT NULL default 'billboard_empty' AFTER name");
