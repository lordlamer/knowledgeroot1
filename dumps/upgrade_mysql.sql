ALTER TABLE `tree` ADD `icon` varchar(255) DEFAULT '' NOT NULL;
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('menu.symlink', '0', 'enable/disable symlink menus', '');
INSERT INTO `extensions` (`keyname`, `active`, `admin`) VALUES ('admin_recover', 1, 1);
ALTER TABLE `content` ADD `createdate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00';
UPDATE `content` SET `createdate`=`lastupdated`;
ALTER TABLE `tree` ADD `defaultcontentposition` int(1) NOT NULL DEFAULT '0';
ALTER TABLE `content` CHANGE `lastupdated` `lastupdated` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
