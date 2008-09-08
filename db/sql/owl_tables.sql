-- This script expects the table prefix to be 'owl_'

-- Session table
CREATE TABLE owl_owl_sessiondata (
  sid        VARCHAR(255) NOT NULL
, stimestamp INT(10)      UNSIGNED NOT NULL
, sdata      TEXT
, PRIMARY KEY (sid)
);

-- User table
CREATE TABLE owl_owl_userdata (
  uid        INTEGER      UNSIGNED NOT NULL AUTO_INCREMENT
, username   VARCHAR(32)  NOT NULL
, password   VARCHAR(128) NOT NULL
, email      VARCHAR(80)
, PRIMARY KEY (uid)
, UNIQUE INDEX (username, password)
, INDEX (username)
);
