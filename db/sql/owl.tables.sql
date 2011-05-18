SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';


-- -----------------------------------------------------
-- Table `owl_applications`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `owl_applications` (
  `aid` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique ID' ,
  `code` VARCHAR(12) NOT NULL COMMENT 'Application code' ,
  `name` VARCHAR(45) NOT NULL COMMENT 'Application name' ,
  `version` VARCHAR(12) NOT NULL COMMENT 'Application version number' ,
  `description` TEXT NULL COMMENT 'Description of the application, can contain HTML code' ,
  `installed` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Indicates if the application is installed on this server' ,
  `enabled` TINYINT UNSIGNED NOT NULL COMMENT 'Indicated is the application has been enabled' ,
  `link` VARCHAR(45) NULL COMMENT 'Link to the applications homepage' ,
  `author` VARCHAR(45) NULL COMMENT 'Author or copyright holder of the application' ,
  `license` VARCHAR(45) NULL COMMENT 'Application license type if applicable' ,
  PRIMARY KEY (`aid`) )
ENGINE = InnoDB, 
COMMENT = 'All known applications' ;

CREATE UNIQUE INDEX `appcode` ON `owl_applications` (`code` ASC) ;


-- -----------------------------------------------------
-- Table `owl_group`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `owl_group` (
  `gid` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique identification' ,
  `groupname` VARCHAR(32) NOT NULL COMMENT 'Name of the group' ,
  `description` TEXT NULL COMMENT 'Optional description of the group' ,
  `aid` INT UNSIGNED NOT NULL COMMENT 'Application the group belongs tor, owl for standard' ,
  PRIMARY KEY (`gid`) ,
  CONSTRAINT `fk_groupapplic`
    FOREIGN KEY (`aid` )
    REFERENCES `owl_applications` (`aid` )
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB, 
COMMENT = 'Standard OWL and application groups' ;

CREATE INDEX `group` ON `owl_group` (`groupname` ASC) ;

CREATE UNIQUE INDEX `applicgroup` ON `owl_group` (`groupname` ASC, `aid` ASC) ;

CREATE INDEX `fk_groupapplic` ON `owl_group` (`aid` ASC) ;


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
  `gid` INT UNSIGNED NOT NULL COMMENT 'Primary group ID' ,
  `right` BIGINT UNSIGNED ZEROFILL NOT NULL DEFAULT 0 COMMENT 'Additional user specific rightbits' ,
  PRIMARY KEY (`uid`) ,
  CONSTRAINT `fk_usergroup`
    FOREIGN KEY (`gid` )
    REFERENCES `owl_group` (`gid` )
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB, 
COMMENT = 'Basic userdata for all OWL based applications' ;

CREATE UNIQUE INDEX `username` USING BTREE ON `owl_user` (`username` ASC) ;

CREATE INDEX `fk_usergroup` ON `owl_user` (`gid` ASC) ;


-- -----------------------------------------------------
-- Table `owl_session`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `owl_session` (
  `sid` VARCHAR(255) NOT NULL COMMENT 'Unique session ID' ,
  `stimestamp` INT(10) NOT NULL COMMENT 'Timestamp of the sessions last activity' ,
  `sdata` TEXT NULL COMMENT 'Room to store the last session data' ,
  PRIMARY KEY (`sid`) )
ENGINE = InnoDB, 
COMMENT = 'This table is used to store all OWL session data' ;


-- -----------------------------------------------------
-- Table `owl_sessionlog`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `owl_sessionlog` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `sid` VARCHAR(255) NOT NULL COMMENT 'Session ID being logged' ,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp of the log message' ,
  `uid` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Current user ID of 0 for anonymous' ,
  `step` INT UNSIGNED NOT NULL COMMENT 'Step count in the current session' ,
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
  `aid` INT UNSIGNED NOT NULL COMMENT 'Application this item belongs to' ,
  `gid` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Group ID for group specific configuration' ,
  `uid` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'User ID for user specific configuration' ,
  `name` VARCHAR(64) NOT NULL COMMENT 'Name of the configuration item' ,
  `value` TEXT NULL COMMENT 'Value for the configuration item' ,
  `protect` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Prevent overwrite at lower level' ,
  `hide` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Indicate value must be hidden from logs' ,
  PRIMARY KEY (`cid`) ,
  CONSTRAINT `fk_configapp`
    FOREIGN KEY (`aid` )
    REFERENCES `owl_applications` (`aid` )
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB, 
COMMENT = 'Dynamic configuration for OWL and applications' ;

CREATE UNIQUE INDEX `configitem` ON `owl_config` (`aid` ASC, `name` ASC) ;

CREATE INDEX `applic` ON `owl_config` (`aid` ASC) ;

CREATE INDEX `group` ON `owl_config` (`gid` ASC) ;

CREATE INDEX `user` ON `owl_config` (`uid` ASC) ;

CREATE INDEX `fk_configapp` ON `owl_config` (`aid` ASC) ;


-- -----------------------------------------------------
-- Table `owl_rights`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `owl_rights` (
  `rid` TINYINT UNSIGNED NOT NULL COMMENT 'Bit identification for this right' ,
  `name` VARCHAR(32) NOT NULL COMMENT 'Name for this right' ,
  `aid` INT UNSIGNED NOT NULL COMMENT 'Application this right is used by or owl for general' ,
  `description` TEXT NULL COMMENT 'An optional description how the rightbit is used' ,
  PRIMARY KEY (`rid`, `aid`) ,
  CONSTRAINT `fk_rightsapp`
    FOREIGN KEY (`aid` )
    REFERENCES `owl_applications` (`aid` )
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB, 
COMMENT = 'Rights that can be granted within owl applications' ;

CREATE UNIQUE INDEX `right` ON `owl_rights` (`name` ASC) ;

CREATE INDEX `fk_rightsapp` ON `owl_rights` (`aid` ASC) ;


-- -----------------------------------------------------
-- Table `owl_memberships`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `owl_memberships` (
  `mid` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique identification' ,
  `uid` INT UNSIGNED NOT NULL COMMENT 'User ID' ,
  `gid` INT UNSIGNED NOT NULL COMMENT 'Group ID' ,
  PRIMARY KEY (`mid`) ,
  CONSTRAINT `fk_groupmember`
    FOREIGN KEY (`gid` )
    REFERENCES `owl_group` (`gid` )
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
  CONSTRAINT `fk_memberuser`
    FOREIGN KEY (`uid` )
    REFERENCES `owl_user` (`uid` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB, 
COMMENT = 'Defenition of all memberships for a user' ;

CREATE INDEX `fk_groupmember` ON `owl_memberships` (`gid` ASC) ;

CREATE INDEX `fk_memberuser` ON `owl_memberships` (`uid` ASC) ;


-- -----------------------------------------------------
-- Table `owl_grouprights`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `owl_grouprights` (
  `gid` INT UNSIGNED NOT NULL COMMENT 'Group ID\n' ,
  `aid` INT UNSIGNED NOT NULL COMMENT 'Application the rights bitmap belongs to' ,
  `right` BIGINT UNSIGNED ZEROFILL NOT NULL COMMENT '64 Right bits' ,
  PRIMARY KEY (`gid`, `aid`) ,
  CONSTRAINT `fk_grouprights_applic`
    FOREIGN KEY (`aid` )
    REFERENCES `owl_applications` (`aid` )
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
  CONSTRAINT `fk_grouprights_group`
    FOREIGN KEY (`gid` )
    REFERENCES `owl_group` (`gid` )
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB, 
COMMENT = 'All application specific rights for each group' ;

CREATE INDEX `fk_grouprights_applic` ON `owl_grouprights` (`aid` ASC) ;

CREATE INDEX `fk_grouprights_group` ON `owl_grouprights` (`gid` ASC) ;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- -----------------------------------------------------
-- Data for table `owl_applications`
-- -----------------------------------------------------
START TRANSACTION;
INSERT INTO owl_applications (`aid`, `code`, `name`, `version`, `description`, `installed`, `enabled`, `link`, `author`, `license`) VALUES (1, 'OWL', 'OWL-PHP', '0.1.0', 'Oveas Web Library for PHP', 1, 1, 'http://oveas.com', 'Oscar van Eijk', NULL);

COMMIT;

-- -----------------------------------------------------
-- Data for table `owl_group`
-- -----------------------------------------------------
START TRANSACTION;
INSERT INTO owl_group (`gid`, `groupname`, `description`, `aid`) VALUES (1, 'nogroup', 'Default group for anonymous users', 1);
INSERT INTO owl_group (`gid`, `groupname`, `description`, `aid`) VALUES (2, 'standard', 'Default group for all registered users', 1);

COMMIT;

-- -----------------------------------------------------
-- Data for table `owl_user`
-- -----------------------------------------------------
START TRANSACTION;
INSERT INTO owl_user (`uid`, `username`, `password`, `email`, `registered`, `verification`, `gid`, `right`) VALUES (2, 'oscar', 'f5a1ee88f62cb3d1cc9d801b5f2910bbb0c3b525', 'oscar@oveas.com', 'NOW()', '', 2, 0);
INSERT INTO owl_user (`uid`, `username`, `password`, `email`, `registered`, `verification`, `gid`, `right`) VALUES (1, 'anonymous', '', '', 'NOW()', '', 1, 0);

COMMIT;

-- -----------------------------------------------------
-- Data for table `owl_config`
-- -----------------------------------------------------
START TRANSACTION;
INSERT INTO owl_config (`cid`, `aid`, `gid`, `uid`, `name`, `value`, `protect`, `hide`) VALUES (NULL, 1, 0, 0, 'locale|date', 'd-M-Y', 0, 0);
INSERT INTO owl_config (`cid`, `aid`, `gid`, `uid`, `name`, `value`, `protect`, `hide`) VALUES (NULL, 1, 0, 0, 'locale|time', 'H:i', 0, 0);
INSERT INTO owl_config (`cid`, `aid`, `gid`, `uid`, `name`, `value`, `protect`, `hide`) VALUES (NULL, 1, 0, 0, 'locale|datetime', 'd-M-Y H:i:s', 0, 0);
INSERT INTO owl_config (`cid`, `aid`, `gid`, `uid`, `name`, `value`, `protect`, `hide`) VALUES (NULL, 1, 0, 0, 'locale|log_date', 'd-m-Y', 0, 0);
INSERT INTO owl_config (`cid`, `aid`, `gid`, `uid`, `name`, `value`, `protect`, `hide`) VALUES (NULL, 1, 0, 0, 'locale|log_time', 'H:i:s.u', 0, 0);
INSERT INTO owl_config (`cid`, `aid`, `gid`, `uid`, `name`, `value`, `protect`, `hide`) VALUES (NULL, 1, 0, 0, 'locale|lang', 'en-UK', 0, 0);
INSERT INTO owl_config (`cid`, `aid`, `gid`, `uid`, `name`, `value`, `protect`, `hide`) VALUES (NULL, 1, 0, 0, 'session|lifetime', '1440', 0, 0);
INSERT INTO owl_config (`cid`, `aid`, `gid`, `uid`, `name`, `value`, `protect`, `hide`) VALUES (NULL, 1, 0, 0, 'session|pwd_minstrength', '2', 0, 0);
INSERT INTO owl_config (`cid`, `aid`, `gid`, `uid`, `name`, `value`, `protect`, `hide`) VALUES (NULL, 1, 0, 0, 'session|check_ip', 'true', 0, 0);
INSERT INTO owl_config (`cid`, `aid`, `gid`, `uid`, `name`, `value`, `protect`, `hide`) VALUES (NULL, 1, 0, 0, 'session|default_user', 'anonymous', 1, 0);
INSERT INTO owl_config (`cid`, `aid`, `gid`, `uid`, `name`, `value`, `protect`, `hide`) VALUES (NULL, 1, 0, 0, 'logging|log_form_data', 'true', 0, 0);
INSERT INTO owl_config (`cid`, `aid`, `gid`, `uid`, `name`, `value`, `protect`, `hide`) VALUES (NULL, 1, 0, 0, 'user|default_group', '2', 0, 0);
INSERT INTO owl_config (`cid`, `aid`, `gid`, `uid`, `name`, `value`, `protect`, `hide`) VALUES (NULL, 1, 0, 0, 'session|default_rights_all', '1', 1, 0);
INSERT INTO owl_config (`cid`, `aid`, `gid`, `uid`, `name`, `value`, `protect`, `hide`) VALUES (NULL, 1, 0, 0, 'mail|driver', 'RawSMTP', 0, 0);

COMMIT;

-- -----------------------------------------------------
-- Data for table `owl_rights`
-- -----------------------------------------------------
START TRANSACTION;
INSERT INTO owl_rights (`rid`, `name`, `aid`, `description`) VALUES (1, 'readpublic', 1, 'Allowed to see all content that has been either unmarked, or marked as public');
INSERT INTO owl_rights (`rid`, `name`, `aid`, `description`) VALUES (2, 'readanonymous', 1, 'Allowed to see anonymous only content');
INSERT INTO owl_rights (`rid`, `name`, `aid`, `description`) VALUES (3, 'readregistered', 1, 'Allowed to see all content that has been marked for registered users');
INSERT INTO owl_rights (`rid`, `name`, `aid`, `description`) VALUES (4, 'modpassword', 1, 'Allowed to change own password');
INSERT INTO owl_rights (`rid`, `name`, `aid`, `description`) VALUES (5, 'modemail', 1, 'Allowed to change own email address');
INSERT INTO owl_rights (`rid`, `name`, `aid`, `description`) VALUES (6, 'modusername', 1, 'Allowed to change own username');
INSERT INTO owl_rights (`rid`, `name`, `aid`, `description`) VALUES (7, 'moduserconfig', 1, 'Allowed to change own configuration settings');
INSERT INTO owl_rights (`rid`, `name`, `aid`, `description`) VALUES (8, 'modgroupconfig', 1, 'Allowed to change configuration settings of the primary group');
INSERT INTO owl_rights (`rid`, `name`, `aid`, `description`) VALUES (9, 'modapplconfig', 1, 'Allowed to change application config settings for OWL');
INSERT INTO owl_rights (`rid`, `name`, `aid`, `description`) VALUES (10, 'addmembers', 1, 'Allowed to add members to the primary group');
INSERT INTO owl_rights (`rid`, `name`, `aid`, `description`) VALUES (11, 'addgroups', 1, 'Allowed to add new groups to OWL');
INSERT INTO owl_rights (`rid`, `name`, `aid`, `description`) VALUES (12, 'managegroupusers', 1, 'Allowed to manage users in the primary group');
INSERT INTO owl_rights (`rid`, `name`, `aid`, `description`) VALUES (13, 'manageusers', 1, 'Allowed to manage all users in OWL');
INSERT INTO owl_rights (`rid`, `name`, `aid`, `description`) VALUES (14, 'installapps', 1, 'Allowed to install new applications');

COMMIT;

-- -----------------------------------------------------
-- Data for table `owl_grouprights`
-- -----------------------------------------------------
START TRANSACTION;
INSERT INTO owl_grouprights (`gid`, `aid`, `right`) VALUES (1, 1, 3);
INSERT INTO owl_grouprights (`gid`, `aid`, `right`) VALUES (2, 1, 93);

COMMIT;
