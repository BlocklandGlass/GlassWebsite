<?php
use Glass\DatabaseManager;
$database = new DatabaseManager();
$database->query("ALTER TABLE `addon_boards` DROP video");
$database->query("ALTER TABLE `addon_boards` ADD icon VARCHAR(24) NOT NULL default 'billboard_empty' AFTER name");
