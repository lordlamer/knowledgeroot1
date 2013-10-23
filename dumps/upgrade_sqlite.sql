ALTER TABLE tree ADD icon text DEFAULT '';
INSERT INTO settings (name, value, description, selection) VALUES ('menu.symlink', '0', 'enable/disable symlink menus', '');
INSERT INTO extensions (keyname, active, admin) VALUES ('admin_recover', 1, 1);
ALTER TABLE content ADD createdate datetime DEFAULT '0000-00-00 00:00:00';
UPDATE content SET createdate=lastupdated;
ALTER TABLE tree ADD defaultcontentposition integer DEFAULT 0;
