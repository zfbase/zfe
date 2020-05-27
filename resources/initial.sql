-- MySQL Script generated by MySQL Workbench
-- Пн 18 мар 2019 21:13:07
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';


-- -----------------------------------------------------
-- Table `editors`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `editors` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `version` INT UNSIGNED NOT NULL DEFAULT '1',
  `creator_id` INT UNSIGNED NULL,
  `editor_id` INT UNSIGNED NULL,
  `datetime_created` TIMESTAMP NULL,
  `datetime_edited` TIMESTAMP NULL,
  `deleted` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
  `second_name` VARCHAR(100) NULL,
  `first_name` VARCHAR(100) NULL,
  `middle_name` VARCHAR(100) NULL,
  `email` VARCHAR(100) NULL,
  `login` VARCHAR(32) NULL,
  `password` VARCHAR(255) NULL,
  `password_salt` VARCHAR(32) NULL,
  `role` VARCHAR(32) NULL,
  `department` VARCHAR(100) NULL,
  `comment` VARCHAR(1000) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `history`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `history` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NULL,
  `datetime_action` TIMESTAMP NULL,
  `table_name` VARCHAR(64) NOT NULL,
  `content_id` INT UNSIGNED NULL,
  `column_name` VARCHAR(64) NULL,
  `content_version` INT UNSIGNED NULL,
  `content_old` VARCHAR(10000) NULL,
  `content_new` VARCHAR(10000) NULL,
  `action_type` TINYINT(1) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `FK_history_editors_1`
    FOREIGN KEY (`user_id`)
    REFERENCES `editors` (`id`)
    ON DELETE SET NULL
    ON UPDATE SET NULL)
ENGINE = InnoDB;

CREATE INDEX `FK_history_editors_1_idx` ON `history` (`user_id` ASC);


-- User for tests
-- tester / topsecurity+
INSERT INTO `editors` (version, creator_id, editor_id, datetime_created, datetime_edited, deleted, status, second_name, first_name, middle_name, email, login, password, password_salt, role, department, comment) VALUES (1, null, null, '2019-04-03 08:04:15', '2019-04-03 08:04:15', 0, 0, 'Tester', 'Actor', null, 'tester@localhost.com', 'tester', '655ed8d6dd71f130e9c9de02f70eab7a', '5ca4cb6f2d8f0', 'admin', null, 'topsecurity+');


-- -----------------------------------------------------
-- Table `tasks`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `tasks` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_at` TIMESTAMP NOT NULL,
  `related_id` INT UNSIGNED NULL,
  `state` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  `scheduled_at` TIMESTAMP NULL,
  `done_at` TIMESTAMP NULL,
  `errors` TEXT NULL,
  `performer_code` VARCHAR(64) NOT NULL,
  `lft` INT UNSIGNED NULL,
  `rgt` INT UNSIGNED NULL,
  `level` INT UNSIGNED NULL,
  `root_id` INT UNSIGNED NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;