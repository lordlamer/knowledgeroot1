BEGIN;

ALTER TABLE tree ADD COLUMN icon varchar(255);
ALTER TABLE tree ALTER COLUMN icon SET DEFAULT '';

INSERT INTO settings (name, value, description, selection) VALUES ('menu.symlink', '0', 'enable/disable symlink menus', '');

INSERT INTO extensions (keyname, active, admin) VALUES ('admin_recover', 1, 1);

ALTER TABLE content ADD COLUMN createdate timestamp;
ALTER TABLE content ALTER COLUMN createdate SET DEFAULT now();
UPDATE content SET createdate=lastupdated;

ALTER TABLE tree ADD COLUMN defaultcontentposition integer;
ALTER TABLE tree ALTER COLUMN defaultcontentposition SET DEFAULT '0';

COMMIT;
