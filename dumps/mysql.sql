CREATE TABLE `access` (
        `id` int(11) NOT NULL auto_increment,
        `table_name` varchar(255) NOT NULL DEFAULT '',
        `belongs_to` int(11) NOT NULL DEFAULT 0,
        `owner_group` varchar(5) NOT NULL DEFAULT '',
        `owner_group_id` int(11) NOT NULL DEFAULT 0,
        `rights` int(11) NOT NULL DEFAULT 0,
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE `content` (
  `id` int(11) NOT NULL auto_increment,
  `title` varchar(255) default NULL,
  `belongs_to` int(11) NOT NULL default '0',
  `content` mediumtext NOT NULL,
  `type` varchar(255) NOT NULL default 'text',
  `sorting` int(11) NOT NULL default '0',
  `owner` int(11) NOT NULL default '0',
  `createdate` datetime NOT NULL default '0000-00-00 00:00:00',
  `lastupdatedby` int(11) NOT NULL default '0',
  `lastupdated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `group` int(11) NOT NULL default '0',
  `userrights` int(11) NOT NULL default '0',
  `grouprights` int(11) NOT NULL default '0',
  `otherrights` int(11) NOT NULL default '0',
  `deleted` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `belongs_to` (`belongs_to`),
  KEY `owner` (`owner`),
  KEY `group` (`group`),
  KEY `userrights` (`userrights`),
  KEY `grouprights` (`grouprights`),
  KEY `otherrights` (`otherrights`),
  KEY `deleted` (`deleted`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE `content_open` (
  `id` int(11) NOT NULL auto_increment,
  `contentid` int(11) NOT NULL default '0',
  `userid`varchar(32) NOT NULL default '',
  `opened` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `contentid` (`contentid`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE `files` (
  `id` int(11) NOT NULL auto_increment,
  `belongs_to` int(11) default NULL,
  `file` longblob,
  `filename` varchar(255) default NULL,
  `filesize` int(11) default NULL,
  `filetype` varchar(255) default 'application/octet-stream',
  `owner` int(11) NOT NULL default '0',
  `date` timestamp NOT NULL,
  `counter` int(11) NOT NULL default '0',
  `deleted` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `belongs_to` (`belongs_to`),
  KEY `deleted` (`deleted`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE `groups` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `enabled` int(1) NOT NULL default '0',
  `deleted` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `deleted` (`deleted`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE `tree` (
  `id` int(11) NOT NULL auto_increment,
  `belongs_to` int(11) NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `tooltip` varchar(255) NOT NULL default '',
  `icon` varchar(255) NOT NULL default '',
  `alias` varchar(255) NOT NULL default '',
  `contentcollapsed` int(1) NOT NULL default '0',
  `defaultcontentposition` int(1) NOT NULL default '0',
  `owner` int(11) NOT NULL default '0',
  `group` int(11) NOT NULL default '0',
  `userrights` int(11) NOT NULL default '0',
  `grouprights` int(11) NOT NULL default '0',
  `otherrights` int(11) NOT NULL default '0',
  `subinheritrights` int(1) NOT NULL default '0',
  `subinheritrightseditable` int(1) NOT NULL default '0',
  `subinheritrightsdisable` int(1) NOT NULL default '0',
  `subinheritowner` int(11) NOT NULL default '0',
  `subinheritgroup` int(11) NOT NULL default '0',
  `subinherituserrights` int(11) NOT NULL default '0',
  `subinheritgrouprights` int(11) NOT NULL default '0',
  `subinheritotherrights` int(11) NOT NULL default '0',
  `symlink` int(11) NOT NULL default '0',
  `sorting` int(11) NOT NULL default '0',
  `deleted` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `belongs_to` (`belongs_to`),
  KEY `owner` (`owner`),
  KEY `group` (`group`),
  KEY `userrights` (`userrights`),
  KEY `grouprights` (`grouprights`),
  KEY `otherrights` (`otherrights`),
  KEY `deleted` (`deleted`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE `user_group` (
  `id` int(11) NOT NULL auto_increment,
  `userid` int(11) NOT NULL default '0',
  `groupid` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `userid` (`userid`),
  KEY `groupid` (`groupid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE `users` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `password` varchar(32) NOT NULL default '',
  `theme` varchar(255) NOT NULL default '',
  `language` varchar(255) NOT NULL default '',
  `enabled` int(1) NOT NULL default '0',
  `defaultgroup` int(11) NOT NULL default '0',
  `defaultrights` int(3) NOT NULL default '0',
  `admin` int(1) NOT NULL default '0',
  `rightedit` int(1) NOT NULL default '0',
  `treecache` text NOT NULL,
  `deleted` int(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `password` (`password`),
  KEY `deleted` (`deleted`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE `extensions` (
`id` INT NOT NULL AUTO_INCREMENT ,
`keyname` VARCHAR( 255 ) NOT NULL ,
`active` INT NOT NULL ,
`admin` INT NOT NULL DEFAULT '0',
`version` VARCHAR(30) NOT NULL DEFAULT '',
PRIMARY KEY ( `id` ),
KEY `active` (`active`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE `settings` (
`id` INT NOT NULL AUTO_INCREMENT ,
`name` VARCHAR( 255 ) NOT NULL ,
`value` text NOT NULL ,
`description` VARCHAR( 255) NOT NULL DEFAULT '',
`selection` VARCHAR(255) NOT NULL DEFAULT '',
PRIMARY KEY ( `id` ) ,
UNIQUE (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE `users_login` (
  `usersid` int(10) unsigned NOT NULL default '0',
  `login_trial` int(10) unsigned NOT NULL default '0',
  `lasttrydate` int(11) unsigned NOT NULL default '0',
  `session_id` varchar(100) NOT NULL default '',
  UNIQUE KEY `usersid` (`usersid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

INSERT INTO `users` ( `id` , `name` , `password` , `enabled` , `defaultgroup` , `defaultrights` , `admin` , `rightedit` , `treecache` )
VALUES (
'1', 'admin', '21232f297a57a5a743894a0e4a801fc3', '1', '1', '210', '1', '1', ''
);

INSERT INTO `groups` ( `id` , `name` , `enabled` )
VALUES (
'1', 'admin', '1'
);

INSERT INTO `groups` ( `id` , `name` , `enabled` )
VALUES (
'2', 'users', '1'
);

# Enable extensions
INSERT INTO `extensions` (`keyname`, `active`, `admin`) VALUES ('admin_extension', 1, 1);
INSERT INTO `extensions` (`keyname`, `active`, `admin`) VALUES ('admin_info', 1, 1);
# INSERT INTO `extensions` (`keyname`, `active`, `admin`) VALUES ('admin_install', 1, 1);
# INSERT INTO `extensions` (`keyname`, `active`, `admin`) VALUES ('admin_update', 1, 1);
INSERT INTO `extensions` (`keyname`, `active`, `admin`) VALUES ('admin_config', 1, 1);
INSERT INTO `extensions` (`keyname`, `active`, `admin`) VALUES ('dojorte', 1, 0);
INSERT INTO `extensions` (`keyname`, `active`, `admin`) VALUES ('admin_recover', 1, 1);

# insert default settings
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('login.delay', '30', 'setting login delay', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('login.max', '50', 'setting max login', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('knowledgeroot.charset', 'UTF-8', 'set charset', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('knowledgeroot.language', 'en', 'set default language', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('knowledgeroot.language_dropdown', '0', 'show language dropdown', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('htmleditor.use', '1', 'enable rte editor', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('htmleditor.editor', 'fckeditor', 'choose fckeditor or tinymce', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('htmleditor.tinymce.cols', '75' , 'set cols for textarea in tinymce', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('htmleditor.tinymce.rows', '20', 'set rows for textarea in tinymce', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('htmleditor.fckeditor.width', '600', 'set widht of fckeditor', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('htmleditor.fckeditor.height', '450', 'set height of fckeditor', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('htmleditor.fckeditor.langdefault', 'en', 'set default lang in default fckeditor', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('htmleditor.fckeditor.langdetect', 'false', 'enable autodetection of language in fckeditor', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('htmleditor.fckeditor.toolbar', 'Default', 'choose toolbar in fckeditor', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('htmleditor.fckeditor.skin', 'default', 'choose skin in fckeditor', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('knowledgeroot.title', 'Knowledgeroot', 'set title for knowledgeroot', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('menu.expandall', '1', 'expand menu at default', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('menu.type', 'static', 'set menu type', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('menu.ajax', 'yes', 'use ajax menu', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('knowledgeroot.default_theme', 'green', 'set theme in knowledgeroot', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('knowledgeroot.showtitle', 'yes', 'show title of contents', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('knowledgeroot.collapsecontent', 'yes', 'enable collapse content', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('knowledgeroot.showlastupdated', 'yes', 'show lastupdate of content', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('knowledgeroot.uploadfolder', 'uploads/', 'set upload folder', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('knowledgeroot.uploadserverpath', 'auto', 'set upload server path', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('knowledgeroot.defaultpage', '', 'set id of default page', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('knowledgeroot.recursivdelete', '2', 'set recusriv delete, 2 - only admins; 1 - all users with login, 0 - all users', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('email.notification', '0', 'enable email notification', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('email.recipients', '', 'set recipients', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('email.type', 'html', 'enable html mails', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('email.from_name', 'Knowledgeroot', 'set from name', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('email.from_email', 'knowledgeroot@mydomain.tld', 'set from email', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('email.smtp_mode', '0', 'enable to use smtp instead of mail', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('email.smtp_host', '', 'set smtp host', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('email.smtp_port', '25', 'set port of host', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('email.smtp_username', '', 'set username for authentification', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('email.smtp_password', '', 'set password for authentification', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('dev.toolbar', '0', 'show developer toolbar', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('pagealias.use', '1', 'enable pagealias', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('pagealias.rights', '2', 'set rights to edit the alias', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('pagealias.static', '0', 'enable of using static links', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('knowledgeroot.maxfilesize', '5242880', 'set maxfilesize for uploads', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('downloads.static', '0', 'enable static download links', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('menu.order.self', '0', 'enable self order of tree elements', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('menu.context', '1', 'enable/disable context menus', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('menu.symlink', '0', 'enable/disable symlink menus', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('menu.dragdrop', '1', 'enable/disable drag and drop in tree', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('menu.edittooltiptext', '0', 'enable/disable edit of tooltiptext of tree element', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('session.handle', '0', 'enable handling of sessin', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('session.lifetime', '60', 'lifetime of session in minutes', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('session.checkIP', '0', 'check ip of session', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('session.checkBrowser', '0', 'check browser of session', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('session.onlyCookies', '0', 'allow only cookie sessions', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('menu.showcounter', '1', 'enable/disable counting of tree items', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('content.showcounter', '1', 'enable/disable counting of content items', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('menu.defaultlayout', '0', 'enable/disable a default layout of the tree', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('menu.defaultlayoutarray', '', 'serialized array with default layout of tree - should not be edit', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('menu.showsourceforgelogo', '0', 'show sourceforge logo', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('knowledgeroot.showlogo', '0', 'show knowledgeroot logo instead of texttitle', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('version', '0.9.9', 'knowledgeroot version - do not edit! - it only shows the database version of knowledgeroot', '');
INSERT INTO `settings` (`name`, `value`, `description`, `selection`) VALUES ('baseurl', '', 'base url for knowledgeroot', '');
