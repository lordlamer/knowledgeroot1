CREATE TABLE access (
        id integer PRIMARY KEY DEFAULT auto_incrementing,
        table_name text DEFAULT '',
        belongs_to integer DEFAULT 0,
        owner_group text DEFAULT '',
        owner_group_id integer DEFAULT 0,
        rights integer DEFAULT 0
);

CREATE TABLE content (
	id integer PRIMARY KEY DEFAULT auto_incrementing,
	title text DEFAULT '',
	belongs_to integer DEFAULT 0,
	content text DEFAULT '',
	type text DEFAULT 'text',
	sorting integer DEFAULT 0,
	owner integer DEFAULT 0,
	"group" integer DEFAULT 0,
	createdate datetime DEFAULT '0000-00-00 00:00:00',
	lastupdatedby integer DEFAULT 0,
	lastupdated datetime DEFAULT '0000-00-00 00:00:00',
	userrights integer DEFAULT 0,
	grouprights integer DEFAULT 0,
	otherrights integer DEFAULT 0,
	deleted integer DEFAULT 0
);

CREATE TABLE content_open (
	id integer PRIMARY KEY DEFAULT auto_incrementing,
	contentid integer DEFAULT 0,
	userid text DEFAULT '',
	opened integer DEFAULT 0
);

CREATE TABLE tree (
	id integer PRIMARY KEY DEFAULT auto_incrementing,
	belongs_to integer DEFAULT 0,
	title text DEFAULT '',
	tooltip text DEFAULT '',
	icon text DEFAULT '',
	alias text DEFAULT '',
	contentcollapsed integer DEFAULT 0,
	defaultcontentposition integer DEFAULT 0,
	owner integer DEFAULT 0,
	"group" integer DEFAULT 0,
	userrights integer DEFAULT 0,
	grouprights integer DEFAULT 0,
	otherrights integer DEFAULT 0,
	subinheritrights integer DEFAULT 0,
	subinheritrightseditable integer DEFAULT 0,
	subinheritrightsdisable integer DEFAULT 0,
	subinheritowner integer DEFAULT 0,
	subinheritgroup integer DEFAULT 0,
	subinherituserrights integer DEFAULT 0,
	subinheritgrouprights integer DEFAULT 0,
	subinheritotherrights integer DEFAULT 0,
	symlink integer DEFAULT 0,
	sorting integer DEFAULT 0,
	deleted integer DEFAULT 0
);

CREATE TABLE files (
	id integer PRIMARY KEY DEFAULT auto_incrementing,
	belongs_to integer DEFAULT 0,
	file blob,
	filename text DEFAULT '',
	filesize integer DEFAULT 0,
	filetype text DEFAULT 'application/octet-stream',
	owner integer DEFAULT 0,
	"date" datetime DEFAULT '0000-00-00 00:00:00',
	counter integer DEFAULT 0,
	deleted integer DEFAULT 0
);

CREATE TABLE users (
	id integer PRIMARY KEY DEFAULT auto_incrementing,
	name text DEFAULT '',
	"password" text DEFAULT '',
	theme text DEFAULT '',
	language text DEFAULT '',
	enabled integer DEFAULT 0,
	defaultgroup integer DEFAULT 0,
	defaultrights text DEFAULT '211',
	admin integer DEFAULT 0,
	rightedit integer DEFAULT 0,
	treecache text,
	deleted integer DEFAULT 0
);

CREATE TABLE groups (
	id integer PRIMARY KEY DEFAULT auto_incrementing,
	name text DEFAULT '',
	enabled integer DEFAULT 0,
	deleted integer DEFAULT 0
);

CREATE TABLE user_group (
	id integer PRIMARY KEY DEFAULT auto_incrementing,
	userid integer DEFAULT 0,
	groupid integer DEFAULT 0
);

CREATE TABLE extensions (
	id integer PRIMARY KEY DEFAULT auto_incrementing,
	keyname text DEFAULT '',
	active integer DEFAULT 0,
	admin integer DEFAULT 0,
	version text DEFAULT ''
);

CREATE TABLE settings (
	id integer PRIMARY KEY DEFAULT auto_incrementing,
	name text NOT NULL,
	value text DEFAULT '',
	description text NOT NULL DEFAULT '',
	selection text NOT NULL DEFAULT '',
	UNIQUE (name)
);

CREATE TABLE users_login (
	usersid integer default 0,
	login_trial integer default 0,
	lasttrydate integer default 0,
	session_id text default '',
	UNIQUE (usersid)
);

INSERT INTO groups (name, enabled) VALUES ('admin', 1);
INSERT INTO groups (name, enabled) VALUES ('users', 1);

INSERT INTO users (name, password, enabled, admin, rightedit, defaultgroup, defaultrights) VALUES ('admin', '21232f297a57a5a743894a0e4a801fc3', 1, 1, 1, (SELECT id FROM groups WHERE name='admin'), 210);

INSERT INTO extensions (keyname, active, admin) VALUES ('admin_extension', 1, 1);
INSERT INTO extensions (keyname, active, admin) VALUES ('admin_info', 1, 1);
-- INSERT INTO extensions (keyname, active, admin) VALUES ('admin_install', 1, 1);
-- INSERT INTO extensions (keyname, active, admin) VALUES ('admin_update', 1, 1);
INSERT INTO extensions (keyname, active, admin) VALUES ('admin_config', 1, 1);
INSERT INTO extensions (keyname, active, admin) VALUES ('dojorte', 1, 0);
INSERT INTO extensions (keyname, active, admin) VALUES ('admin_recover', 1, 1);

INSERT INTO settings (name, value, description, selection) VALUES ('login.delay', '30', 'setting login delay', '');
INSERT INTO settings (name, value, description, selection) VALUES ('login.max', '50', 'setting max login', '');
INSERT INTO settings (name, value, description, selection) VALUES ('knowledgeroot.charset', 'UTF-8', 'set charset', '');
INSERT INTO settings (name, value, description, selection) VALUES ('knowledgeroot.language', 'en', 'set default language', '');
INSERT INTO settings (name, value, description, selection) VALUES ('knowledgeroot.language_dropdown', '0', 'show language dropdown', '');
INSERT INTO settings (name, value, description, selection) VALUES ('htmleditor.use', '1', 'enable rte editor', '');
INSERT INTO settings (name, value, description, selection) VALUES ('htmleditor.editor', 'fckeditor', 'choose fckeditor or tinymce', '');
INSERT INTO settings (name, value, description, selection) VALUES ('htmleditor.tinymce.cols', '75' , 'set cols for textarea in tinymce', '');
INSERT INTO settings (name, value, description, selection) VALUES ('htmleditor.tinymce.rows', '20', 'set rows for textarea in tinymce', '');
INSERT INTO settings (name, value, description, selection) VALUES ('htmleditor.fckeditor.width', '600', 'set widht of fckeditor', '');
INSERT INTO settings (name, value, description, selection) VALUES ('htmleditor.fckeditor.height', '450', 'set height of fckeditor', '');
INSERT INTO settings (name, value, description, selection) VALUES ('htmleditor.fckeditor.langdefault', 'en', 'set default lang in default fckeditor', '');
INSERT INTO settings (name, value, description, selection) VALUES ('htmleditor.fckeditor.langdetect', 'false', 'enable autodetection of language in fckeditor', '');
INSERT INTO settings (name, value, description, selection) VALUES ('htmleditor.fckeditor.toolbar', 'Default', 'choose toolbar in fckeditor', '');
INSERT INTO settings (name, value, description, selection) VALUES ('htmleditor.fckeditor.skin', 'default', 'choose skin in fckeditor', '');
INSERT INTO settings (name, value, description, selection) VALUES ('knowledgeroot.title', 'Knowledgeroot', 'set title for knowledgeroot', '');
INSERT INTO settings (name, value, description, selection) VALUES ('menu.expandall', '1', 'expand menu at default', '');
INSERT INTO settings (name, value, description, selection) VALUES ('menu.type', 'static', 'set menu type', '');
INSERT INTO settings (name, value, description, selection) VALUES ('menu.ajax', 'yes', 'use ajax menu', '');
INSERT INTO settings (name, value, description, selection) VALUES ('knowledgeroot.default_theme', 'green', 'set theme in knowledgeroot', '');
INSERT INTO settings (name, value, description, selection) VALUES ('knowledgeroot.showtitle', 'yes', 'show title of contents', '');
INSERT INTO settings (name, value, description, selection) VALUES ('knowledgeroot.collapsecontent', 'yes', 'enable collapse content', '');
INSERT INTO settings (name, value, description, selection) VALUES ('knowledgeroot.showlastupdated', 'yes', 'show lastupdate of content', '');
INSERT INTO settings (name, value, description, selection) VALUES ('knowledgeroot.uploadfolder', 'uploads/', 'set upload folder', '');
INSERT INTO settings (name, value, description, selection) VALUES ('knowledgeroot.uploadserverpath', 'auto', 'set upload server path', '');
INSERT INTO settings (name, value, description, selection) VALUES ('knowledgeroot.defaultpage', '', 'set id of default page', '');
INSERT INTO settings (name, value, description, selection) VALUES ('knowledgeroot.recursivdelete', '2', 'set recusriv delete, 2 - only admins; 1 - all users with login, 0 - all users', '');
INSERT INTO settings (name, value, description, selection) VALUES ('email.notification', '0', 'enable email notification', '');
INSERT INTO settings (name, value, description, selection) VALUES ('email.recipients', '', 'set recipients', '');
INSERT INTO settings (name, value, description, selection) VALUES ('email.type', 'html', 'enable html mails', '');
INSERT INTO settings (name, value, description, selection) VALUES ('email.from_name', 'Knowledgeroot', 'set from name', '');
INSERT INTO settings (name, value, description, selection) VALUES ('email.from_email', 'knowledgeroot@mydomain.tld', 'set from email', '');
INSERT INTO settings (name, value, description, selection) VALUES ('email.smtp_mode', '0', 'enable to use smtp instead of mail', '');
INSERT INTO settings (name, value, description, selection) VALUES ('email.smtp_host', '', 'set smtp host', '');
INSERT INTO settings (name, value, description, selection) VALUES ('email.smtp_port', '25', 'set port of host', '');
INSERT INTO settings (name, value, description, selection) VALUES ('email.smtp_username', '', 'set username for authentification', '');
INSERT INTO settings (name, value, description, selection) VALUES ('email.smtp_password', '', 'set password for authentification', '');
INSERT INTO settings (name, value, description, selection) VALUES ('dev.toolbar', '0', 'show developer toolbar', '');
INSERT INTO settings (name, value, description, selection) VALUES ('pagealias.use', '1', 'enable pagealias', '');
INSERT INTO settings (name, value, description, selection) VALUES ('pagealias.rights', '2', 'set rights to edit the alias', '');
INSERT INTO settings (name, value, description, selection) VALUES ('pagealias.static', '0', 'enable of using static links', '');
INSERT INTO settings (name, value, description, selection) VALUES ('knowledgeroot.maxfilesize', '5242880', 'set maxfilesize for uploads', '');
INSERT INTO settings (name, value, description, selection) VALUES ('downloads.static', '0', 'enable static download links', '');
INSERT INTO settings (name, value, description, selection) VALUES ('menu.order.self', '0', 'enable self ordering of tree elments', '');
INSERT INTO settings (name, value, description, selection) VALUES ('menu.context', '1', 'enable/disable contextmenus', '');
INSERT INTO settings (name, value, description, selection) VALUES ('menu.symlink', '0', 'enable/disable symlink menus', '');
INSERT INTO settings (name, value, description, selection) VALUES ('menu.dragdrop', '1', 'enable/disable drag and drop in tree', '');
INSERT INTO settings (name, value, description, selection) VALUES ('menu.edittooltiptext', '0', 'enable/disable edit of tooltiptext of tree element', '');
INSERT INTO settings (name, value, description, selection) VALUES ('session.handle', '0', 'enable handling of sessin', '');
INSERT INTO settings (name, value, description, selection) VALUES ('session.lifetime', '60', 'lifetime of session in minutes', '');
INSERT INTO settings (name, value, description, selection) VALUES ('session.checkIP', '0', 'check ip of session', '');
INSERT INTO settings (name, value, description, selection) VALUES ('session.checkBrowser', '0', 'check browser of session', '');
INSERT INTO settings (name, value, description, selection) VALUES ('session.onlyCookies', '0', 'allow only cookie sessions', '');
INSERT INTO settings (name, value, description, selection) VALUES ('menu.showcounter', '1', 'enable/disable counting of tree items', '');
INSERT INTO settings (name, value, description, selection) VALUES ('content.showcounter', '1', 'enable/disable counting of content items', '');
INSERT INTO settings (name, value, description, selection) VALUES ('menu.defaultlayout', '0', 'enable/disable a default layout of the tree', '');
INSERT INTO settings (name, value, description, selection) VALUES ('menu.defaultlayoutarray', '', 'serialized array with default layout of tree - should not be edit', '');
INSERT INTO settings (name, value, description, selection) VALUES ('menu.showsourceforgelogo', '0', 'show sourceforge logo', '');
INSERT INTO settings (name, value, description, selection) VALUES ('knowledgeroot.showlogo', '0', 'show knowledgeroot logo instead of texttitle', '');
INSERT INTO settings (name, value, description, selection) VALUES ('version', '0.9.9', 'knowledgeroot version - do not edit! - it only shows the database version of knowledgeroot', '');
INSERT INTO settings (name, value, description, selection) VALUES ('baseurl', '', 'base url for knowledgeroot', '');

/*
CREATE INDEX idx_content ON content USING btree (belongs_to);
CREATE INDEX idx_content_owner ON content USING btree (owner);
CREATE INDEX idx_content_group ON content USING btree ("group");
CREATE INDEX idx_content_userrights ON content USING btree (userrights);
CREATE INDEX idx_content_grouprights ON content USING btree (grouprights);
CREATE INDEX idx_content_otherrights ON content USING btree (otherrights);
CREATE INDEX idx_content_deleted ON content USING btree (deleted);

CREATE INDEX idx_content_open_contentid ON content_open USING btree (contentid);
CREATE INDEX idx_content_open_userid ON content_open USING btree (userid);

CREATE INDEX idx_files ON files USING btree (belongs_to);
CREATE INDEX idx_files_deleted ON files USING btree (deleted);

CREATE INDEX idx_groups_deleted ON groups USING btree (deleted);

CREATE INDEX idx_tree ON tree USING btree (belongs_to);
CREATE INDEX idx_tree_owner ON tree USING btree (owner);
CREATE INDEX idx_tree_group ON tree USING btree ("group");
CREATE INDEX idx_tree_userrights ON tree USING btree (userrights);
CREATE INDEX idx_tree_grouprights ON tree USING btree (grouprights);
CREATE INDEX idx_tree_otherrights ON tree USING btree (otherrights);
CREATE INDEX idx_tree_deleted ON tree USING btree (deleted);

CREATE INDEX idx_user_group_userid ON user_group USING btree (userid);
CREATE INDEX idx_user_group_groupid ON user_group USING btree (groupid);

CREATE INDEX idx_users ON users USING btree (name);
CREATE INDEX idx_users_password ON users USING btree (password);
CREATE INDEX idx_users_deleted ON users USING btree (deleted);

CREATE INDEX idx_extensions ON extensions USING btree (active);
*/
