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



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- -----------------------------------------------------
-- Data for table `owl_user`
-- -----------------------------------------------------
SET AUTOCOMMIT=0;
INSERT INTO owl_user (`uid`, `username`, `password`, `email`) VALUES (NULL, 'oscar', 'f5a1ee88f62cb3d1cc9d801b5f2910bbb0c3b525', 'oscar@oveas.com');

COMMIT;
