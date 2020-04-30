CREATE TABLE `tasks` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Идентификатор',
  `datetime_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата и время добавления',
  `datetime_schedule` TIMESTAMP NULL DEFAULT NULL COMMENT 'Дата и время планового запуска',
  `datetime_started` TIMESTAMP NULL DEFAULT NULL COMMENT 'Дата и время начала исполнения',
  `datetime_done` TIMESTAMP NULL DEFAULT NULL COMMENT 'Дата и время окончания исполнения',
  `datetime_canceled` TIMESTAMP NULL DEFAULT NULL COMMENT 'Дата и время отменены',
  `priority` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'Приоритет',
  `performer_code` VARCHAR(63) NOT NULL COMMENT 'Код исполнителя',
  `related_id` INT(10) UNSIGNED NOT NULL COMMENT 'Объект исполнения',
  `parent_id` INT(10) UNSIGNED NULL DEFAULT NULL COMMENT 'Родительская задача',
  `revision` TINYINT(3) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Номер попытки исполнения',
  `return_code` TINYINT(3) UNSIGNED DEFAULT NULL COMMENT 'Код результата исполнения',
  `errors` VARCHAR(1000) DEFAULT NULL COMMENT 'Ошибки',
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;
