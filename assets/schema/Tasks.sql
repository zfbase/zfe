CREATE TABLE `tasks` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор',
  `datetime_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата добавления',
  `datetime_schedule` TIMESTAMP NULL DEFAULT NULL COMMENT 'Плановое время запуска',
  `datetime_done` TIMESTAMP NULL DEFAULT NULL COMMENT 'Окончание исполнения',
  `performer_code` VARCHAR(63) DEFAULT NULL COMMENT 'Код исполнителя',
  `related_id` INT(10) UNSIGNED NOT NULL COMMENT 'Объект исполнения',
  `parent_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Родительская задача',
  `revision` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Номер попытки исполнения',
  `state` TINYINT(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Состояние',
  `errors` VARCHAR(1000) DEFAULT NULL COMMENT 'Ошибки',
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;
