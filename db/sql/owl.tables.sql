SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';


-- -----------------------------------------------------
-- Table `owl_user`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `owl_user` (
  `uid` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Internally user user identification' ,
  `username` VARCHAR(32) NOT NULL COMMENT 'Username, must be unique' ,
  `password` VARCHAR(128) NOT NULL COMMENT 'Encrypted password' ,
  `email` VARCHAR(45) NULL COMMENT 'Email address. Extra addresses must be handled by the apps' ,
  `registered` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'First reghistration date and time' ,
  `verification` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'Verification code for new registrations' ,
  PRIMARY KEY (`uid`) )
ENGINE = InnoDB
COMMENT = 'Basic userdata for all OWL based applications';

CREATE UNIQUE INDEX `username` USING BTREE ON `owl_user` (`username` ASC) ;


-- -----------------------------------------------------
-- Table `owl_session`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `owl_session` (
  `sid` VARCHAR(255) NOT NULL COMMENT 'Unique session ID' ,
  `stimestamp` INT(10) NOT NULL COMMENT 'Timestamp of the sessions last activity' ,
  `sdata` TEXT NULL COMMENT 'Room to store the last session data' ,
  PRIMARY KEY (`sid`) )
ENGINE = InnoDB
COMMENT = 'This table is used to store all OWL session data';


-- -----------------------------------------------------
-- Table `owl_sessionlog`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `owl_sessionlog` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `sid` VARCHAR(255) NOT NULL COMMENT 'Session ID being logged' ,
  `step` INT UNSIGNED NOT NULL COMMENT 'Step count in the current session' ,
  `uid` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Current user ID of 0 for anonymous' ,
  `applic` VARCHAR(32) NOT NULL COMMENT 'Current application name' ,
  `ip` VARCHAR(32) NOT NULL COMMENT 'Client IP address' ,
  `referer` VARCHAR(255) NULL COMMENT 'Refering URL' ,
  `dispatcher` VARCHAR(255) NULL COMMENT 'Decoded dispatcher' ,
  `formdata` LONGBLOB NULL COMMENT 'Full formdata' ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `owl_config`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `owl_config` (
  `cid` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `applic` VARCHAR(32) NOT NULL DEFAULT 'owl' COMMENT 'Application this item belongs to' ,
  `gid` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Group ID for group specific configuration' ,
  `uid` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'User ID for user specific configuration' ,
  `name` VARCHAR(64) NOT NULL COMMENT 'Name of the configuration item' ,
  `value` TEXT NULL COMMENT 'Value for the configuration item' ,
  `protect` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Prevent overwrite at lower level' ,
  `hide` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Indicate value must be hidden from logs' ,
  PRIMARY KEY (`cid`) )
ENGINE = InnoDB
COMMENT = 'Dynamic configuration for OWL and applications';

CREATE UNIQUE INDEX `configitem` ON `owl_config` (`applic` ASC, `name` ASC) ;

CREATE INDEX `applic` ON `owl_config` (`applic` ASC) ;

CREATE INDEX `group` ON `owl_config` (`gid` ASC) ;

CREATE INDEX `user` ON `owl_config` (`uid` ASC) ;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- -----------------------------------------------------
-- Data for table `owl_user`
-- -----------------------------------------------------
SET AUTOCOMMIT=0;
INSERT INTO owl_user (`uid`, `username`, `password`, `email`, `registered`, `verification`) VALUES (NULL, 'oscar', 'f5a1ee88f62cb3d1cc9d801b5f2910bbb0c3b525', 'oscar@oveas.com', 'NOW()', '');

COMMIT;

-- -----------------------------------------------------
-- Data for table `owl_config`
-- -----------------------------------------------------
SET AUTOCOMMIT=0;
INSERT INTO owl_config (`cid`, `applic`, `gid`, `uid`, `name`, `value`, `protect`, `hide`) VALUES (NULL, 'owl', 0, 0, 'locale|date', 'd-M-Y', 0, 0);
INSERT INTO owl_config (`cid`, `applic`, `gid`, `uid`, `name`, `value`, `protect`, `hide`) VALUES (NULL, 'owl', 0, 0, 'locale|time', 'H:i', 0, 0);
INSERT INTO owl_config (`cid`, `applic`, `gid`, `uid`, `name`, `value`, `protect`, `hide`) VALUES (NULL, 'owl', 0, 0, 'locale|datetime', 'd-M-Y H:i:s', 0, 0);
INSERT INTO owl_config (`cid`, `applic`, `gid`, `uid`, `name`, `value`, `protect`, `hide`) VALUES (NULL, 'owl', 0, 0, 'locale|log_date', 'd-m-Y', 0, 0);
INSERT INTO owl_config (`cid`, `applic`, `gid`, `uid`, `name`, `value`, `protect`, `hide`) VALUES (NULL, 'owl', 0, 0, 'locale|log_time', 'H:i:s.u', 0, 0);
INSERT INTO owl_config (`cid`, `applic`, `gid`, `uid`, `name`, `value`, `protect`, `hide`) VALUES (NULL, 'owl', 0, 0, 'locale|lang', 'en-uk', 0, 0);
INSERT INTO owl_config (`cid`, `applic`, `gid`, `uid`, `name`, `value`, `protect`, `hide`) VALUES (NULL, 'owl', 0, 0, 'session|lifetime', '1440', 0, 0);
INSERT INTO owl_config (`cid`, `applic`, `gid`, `uid`, `name`, `value`, `protect`, `hide`) VALUES (NULL, 'owl', 0, 0, 'session|pwd_minstrength', '2', 0, 0);
INSERT INTO owl_config (`cid`, `applic`, `gid`, `uid`, `name`, `value`, `protect`, `hide`) VALUES (NULL, 'owl', 0, 0, 'session|check_ip', 'true', 0, 0);
INSERT INTO owl_config (`cid`, `applic`, `gid`, `uid`, `name`, `value`, `protect`, `hide`) VALUES (NULL, 'owl', 0, 0, 'session|default_user', 'anonymous', 1, 0);

COMMIT;
