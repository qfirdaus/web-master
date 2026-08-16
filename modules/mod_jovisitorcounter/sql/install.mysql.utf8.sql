-- Create #__jovisitorcounter table with indexes
CREATE TABLE IF NOT EXISTS `#__jovisitorcounter` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `ip_address` VARCHAR(45) NOT NULL,
    `country` VARCHAR(100) NOT NULL DEFAULT 'Unknown',
    `country_code` VARCHAR(10) NOT NULL DEFAULT 'unknown',
    `visit_time` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_ip_address` (`ip_address`),
    INDEX `idx_visit_time` (`visit_time`),
    INDEX `idx_country_code` (`country_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Create #__jovisitorcounter_summary table
CREATE TABLE IF NOT EXISTS `#__jovisitorcounter_summary` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `total_visitors` INT(11) NOT NULL DEFAULT 0,
    `today` INT(11) NOT NULL DEFAULT 0,
    `yesterday` INT(11) NOT NULL DEFAULT 0,
    `this_week` INT(11) NOT NULL DEFAULT 0,
    `last_week` INT(11) NOT NULL DEFAULT 0,
    `this_month` INT(11) NOT NULL DEFAULT 0,
    `last_month` INT(11) NOT NULL DEFAULT 0,
    `two_months_ago` INT(11) NOT NULL DEFAULT 0,
    `three_months_ago` INT(11) NOT NULL DEFAULT 0,
	`four_months_ago` INT(11) NOT NULL DEFAULT 0,
	`five_months_ago` INT(11) NOT NULL DEFAULT 0,
    `last_six_months` INT(11) NOT NULL DEFAULT 0,
    `this_year` INT(11) NOT NULL DEFAULT 0,
    `last_year` INT(11) NOT NULL DEFAULT 0,
    `last_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default row only if the table is empty
INSERT INTO `#__jovisitorcounter_summary`
(`total_visitors`, `today`, `yesterday`, `this_week`, `last_week`, `this_month`, `last_month`, `two_months_ago`, `three_months_ago`, `four_months_ago`, `five_months_ago`, `last_six_months`, `this_year`, `last_year`)
SELECT 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `#__jovisitorcounter_summary`
);

-- Create #__jovisitorcounter_country_summary table with indexes
CREATE TABLE IF NOT EXISTS `#__jovisitorcounter_country_summary` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `country_code` VARCHAR(10) NOT NULL DEFAULT 'unknown',
    `country_name` VARCHAR(100) NOT NULL DEFAULT 'Unknown',
    `total_visitors` INT(11) NOT NULL DEFAULT 0,
    `today` INT(11) NOT NULL DEFAULT 0,
    `yesterday` INT(11) NOT NULL DEFAULT 0,
    `this_week` INT(11) NOT NULL DEFAULT 0,
    `last_week` INT(11) NOT NULL DEFAULT 0,
    `this_month` INT(11) NOT NULL DEFAULT 0,
    `last_month` INT(11) NOT NULL DEFAULT 0,
    `last_six_months` INT(11) NOT NULL DEFAULT 0,
    `this_year` INT(11) NOT NULL DEFAULT 0,
    `last_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `country_code` (`country_code`),
    INDEX `idx_last_updated` (`last_updated`),
    INDEX `idx_total_visitors_last_updated` (`total_visitors`, `last_updated`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Populate #__jovisitorcounter_country_summary with initial data only if table is empty
INSERT INTO `#__jovisitorcounter_country_summary` (`country_code`, `country_name`)
SELECT DISTINCT `country_code`, `country`
FROM `#__jovisitorcounter`
WHERE `country_code` IS NOT NULL AND `country_code` != ''
AND (SELECT COUNT(*) FROM `#__jovisitorcounter_country_summary`) = 0;