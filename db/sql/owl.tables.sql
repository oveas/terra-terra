SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';


-- -----------------------------------------------------
-- Table `owl_applications`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `owl_applications` ;

CREATE  TABLE IF NOT EXISTS `owl_applications` (
  `aid` INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Unique ID' ,
  `code` VARCHAR(12) NOT NULL COMMENT 'Application code' ,
  `url` VARCHAR(45) NOT NULL ,
  `name` VARCHAR(45) NOT NULL COMMENT 'Application name' ,
  `version` VARCHAR(12) NOT NULL COMMENT 'Application version number' ,
  `description` TEXT NULL COMMENT 'Description of the application, can contain HTML code' ,
  `installed` TINYINT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Indicates if the application is installed on this server' ,
  `enabled` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Indicated is the application has been enabled' ,
  `link` VARCHAR(45) NULL COMMENT 'Link to the applications homepage' ,
  `author` VARCHAR(45) NULL COMMENT 'Author or copyright holder of the application' ,
  `license` VARCHAR(45) NULL COMMENT 'Application license type if applicable' ,
  PRIMARY KEY (`aid`) )
ENGINE = InnoDB, 
COMMENT = 'All known applications' ;

CREATE UNIQUE INDEX `app_appcode` ON `owl_applications` (`code` ASC) ;


-- -----------------------------------------------------
-- Table `owl_group`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `owl_group` ;

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

CREATE INDEX `grp_group` ON `owl_group` (`groupname` ASC) ;

CREATE UNIQUE INDEX `grp_applicgroup` ON `owl_group` (`groupname` ASC, `aid` ASC) ;

CREATE INDEX `fk_groupapplic` ON `owl_group` (`aid` ASC) ;


-- -----------------------------------------------------
-- Table `owl_user`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `owl_user` ;

CREATE  TABLE IF NOT EXISTS `owl_user` (
  `uid` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Internally user user identification' ,
  `username` VARCHAR(32) NOT NULL COMMENT 'Username, must be unique' ,
  `password` VARCHAR(128) NULL COMMENT 'Encrypted password' ,
  `email` VARCHAR(45) NULL COMMENT 'Email address. Extra addresses must be handled by the apps' ,
  `registered` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() COMMENT 'First registration date and time' ,
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

CREATE UNIQUE INDEX `usr_username` USING BTREE ON `owl_user` (`username` ASC) ;

CREATE INDEX `fk_usergroup` ON `owl_user` (`gid` ASC) ;


-- -----------------------------------------------------
-- Table `owl_session`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `owl_session` ;

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
DROP TABLE IF EXISTS `owl_sessionlog` ;

CREATE  TABLE IF NOT EXISTS `owl_sessionlog` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `sid` VARCHAR(255) NOT NULL COMMENT 'Session ID being logged' ,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() COMMENT 'Timestamp of the log message' ,
  `uid` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Current user ID of 0 for anonymous' ,
  `step` INT UNSIGNED NOT NULL COMMENT 'Step count in the current session' ,
  `applic` VARCHAR(32) NOT NULL COMMENT 'Current application name' ,
  `ip` VARCHAR(32) NOT NULL COMMENT 'Client IP address' ,
  `referer` VARCHAR(255) NULL COMMENT 'Refering URL' ,
  `dispatcher` VARCHAR(255) NULL COMMENT 'Decoded dispatcher' ,
  `formdata` LONGTEXT NULL COMMENT 'Full formdata' ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `owl_config_sections`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `owl_config_sections` ;

CREATE  TABLE IF NOT EXISTS `owl_config_sections` (
  `sid` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(45) NOT NULL ,
  PRIMARY KEY (`sid`) )
ENGINE = InnoDB, 
COMMENT = 'Configuration sections' ;


-- -----------------------------------------------------
-- Table `owl_config`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `owl_config` ;

CREATE  TABLE IF NOT EXISTS `owl_config` (
  `cid` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `aid` INT UNSIGNED NOT NULL COMMENT 'Application this item belongs to' ,
  `gid` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Group ID for group specific configuration' ,
  `uid` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'User ID for user specific configuration' ,
  `sid` INT NOT NULL DEFAULT 0 COMMENT 'Configuration section for this item' ,
  `name` VARCHAR(64) NOT NULL COMMENT 'Name of the configuration item' ,
  `value` TEXT NULL COMMENT 'Value for the configuration item' ,
  `protect` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Prevent overwrite at lower level' ,
  `hide` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Indicate value must be hidden from logs' ,
  PRIMARY KEY (`cid`) ,
  CONSTRAINT `fk_configapp`
    FOREIGN KEY (`aid` )
    REFERENCES `owl_applications` (`aid` )
    ON DELETE RESTRICT
    ON UPDATE RESTRICT,
  CONSTRAINT `fk_configsect`
    FOREIGN KEY (`sid` )
    REFERENCES `owl_config_sections` (`sid` )
    ON DELETE RESTRICT
    ON UPDATE RESTRICT)
ENGINE = InnoDB, 
COMMENT = 'Dynamic configuration for OWL and applications' ;

CREATE UNIQUE INDEX `cnf_configitem` ON `owl_config` (`aid` ASC, `name` ASC) ;

CREATE INDEX `cnf_applic` ON `owl_config` (`aid` ASC) ;

CREATE INDEX `cnf_group` ON `owl_config` (`gid` ASC) ;

CREATE INDEX `cnf_user` ON `owl_config` (`uid` ASC) ;

CREATE INDEX `fk_configapp` ON `owl_config` (`aid` ASC) ;

CREATE INDEX `fk_configsect` ON `owl_config` (`sid` ASC) ;


-- -----------------------------------------------------
-- Table `owl_rights`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `owl_rights` ;

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

CREATE UNIQUE INDEX `rgt_right` ON `owl_rights` (`name` ASC) ;

CREATE INDEX `fk_rightsapp` ON `owl_rights` (`aid` ASC) ;


-- -----------------------------------------------------
-- Table `owl_memberships`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `owl_memberships` ;

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
DROP TABLE IF EXISTS `owl_grouprights` ;

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
-- Data for table `owl_user`
-- -----------------------------------------------------
START TRANSACTION;
INSERT INTO owl_user (`uid`, `username`, `password`, `email`, `registered`, `verification`, `gid`, `right`) VALUES (2, 'owl', 'c90722aca1011e147b21ad2c3bb0a205e1026497', 'owluser@localhost.local', NULL, '', 2, 0);
INSERT INTO owl_user (`uid`, `username`, `password`, `email`, `registered`, `verification`, `gid`, `right`) VALUES (1, 'anonymous', '', '', NULL, '', 1, 0);

COMMIT;

-- -----------------------------------------------------
-- Data for table `owl_config_sections`
-- -----------------------------------------------------
START TRANSACTION;
INSERT INTO owl_config_sections (`sid`, `name`) VALUES (1, 'general');
INSERT INTO owl_config_sections (`sid`, `name`) VALUES (2, 'database');
INSERT INTO owl_config_sections (`sid`, `name`) VALUES (3, 'logging');
INSERT INTO owl_config_sections (`sid`, `name`) VALUES (4, 'session');
INSERT INTO owl_config_sections (`sid`, `name`) VALUES (5, 'user');
INSERT INTO owl_config_sections (`sid`, `name`) VALUES (6, 'locale');
INSERT INTO owl_config_sections (`sid`, `name`) VALUES (7, 'mail');

COMMIT;
