-- This script expects the table prefix to be 'owl_'

-- Session table
CREATE TABLE owl_owl_sessiondata (
  sid        VARCHAR(255) NOT NULL
, stimestamp INT(10)      UNSIGNED NOT NULL
, sdata      TEXT
, PRIMARY KEY (sid)
);
