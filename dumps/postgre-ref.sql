BEGIN;

CREATE SEQUENCE seq_knowledge;

CREATE TABLE users (
	id integer PRIMARY KEY DEFAULT nextval('seq_knowledge'),
	name varchar(255) DEFAULT '',
	"password" varchar(255) DEFAULT '',
	enabled integer DEFAULT 0,
	defaultgroup integer DEFAULT 0,
	defaultrights varchar(3) DEFAULT '211',
	admin integer DEFAULT 0,
	rightedit integer DEFAULT 0,
	treecache text,
	deleted integer DEFAULT 0
);

CREATE TABLE groups (
	id integer PRIMARY KEY DEFAULT nextval('seq_knowledge'),
	name varchar(255) DEFAULT '',
	enabled integer DEFAULT 0,
	deleted integer DEFAULT 0
);

CREATE TABLE user_group (
	id integer PRIMARY KEY DEFAULT nextval('seq_knowledge'),
	userid integer DEFAULT 0 REFERENCES users (id) ON DELETE RESTRICT,
	groupid integer DEFAULT 0 REFERENCES groups (id) ON DELETE RESTRICT
);

CREATE TABLE tree (
	id integer PRIMARY KEY DEFAULT nextval('seq_knowledge'),
	belongs_to integer DEFAULT 0,
	title varchar(255) DEFAULT '',
	owner integer DEFAULT 0 REFERENCES users (id) ON DELETE RESTRICT,
	"group" integer DEFAULT 0 REFERENCES groups (id) ON DELETE RESTRICT,
	userrights integer DEFAULT 0,
	grouprights integer DEFAULT 0,
	otherrights integer DEFAULT 0,
	deleted integer DEFAULT 0
);

CREATE TABLE content (
	id integer PRIMARY KEY DEFAULT nextval('seq_knowledge'),
	belongs_to integer DEFAULT 0 REFERENCES tree (id) ON DELETE RESTRICT,
	content text DEFAULT '',
	owner integer DEFAULT 0 REFERENCES users (id) ON DELETE RESTRICT,
	"group" integer DEFAULT 0 REFERENCES groups (id) ON DELETE RESTRICT,
	userrights integer DEFAULT 0,
	grouprights integer DEFAULT 0,
	otherrights integer DEFAULT 0,
	deleted integer DEFAULT 0
);

CREATE TABLE files (
	id integer PRIMARY KEY DEFAULT nextval('seq_knowledge'),
	belongs_to integer DEFAULT 0 REFERENCES content (id) ON DELETE RESTRICT,
	object oid,
	filename varchar(255) DEFAULT '',
	filesize integer DEFAULT 0,
	filetype varchar(255) DEFAULT 'application/octet-stream',
	owner integer DEFAULT 0 REFERENCES users (id) ON DELETE RESTRICT,
	date timestamp DEFAULT now(),
	deleted integer DEFAULT 0
);

CREATE INDEX idx_content ON content USING btree (belongs_to);
CREATE INDEX idx_tree ON tree USING btree (belongs_to);
CREATE INDEX idx_files ON files USING btree (belongs_to);
CREATE INDEX idx_users ON users USING btree (name);

INSERT INTO groups (name, enabled) VALUES ('admin', 1);
INSERT INTO groups (name, enabled) VALUES ('users', 1);

INSERT INTO users (name, password, enabled, admin, rightedit, defaultgroup, defaultrights) VALUES ('admin', '21232f297a57a5a743894a0e4a801fc3', 1, 1, 1, (SELECT id FROM groups WHERE name='admin'), 210);


COMMIT;