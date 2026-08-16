-- Upgrade from v1.0.x to v2.0.0

-- Modify country column
ALTER TABLE `#__jovisitorcounter`
    MODIFY `country` VARCHAR(100) NOT NULL DEFAULT 'Unknown';

-- Modify country_code column
ALTER TABLE `#__jovisitorcounter`
    MODIFY `country_code` VARCHAR(10) NOT NULL DEFAULT 'unknown';

-- Add new summary columns
ALTER TABLE `#__jovisitorcounter_summary`
    ADD COLUMN `two_months_ago` INT NOT NULL DEFAULT 0 AFTER `last_month`,
    ADD COLUMN `three_months_ago` INT NOT NULL DEFAULT 0 AFTER `two_months_ago`,
    ADD COLUMN `four_months_ago` INT NOT NULL DEFAULT 0 AFTER `three_months_ago`,
    ADD COLUMN `five_months_ago` INT NOT NULL DEFAULT 0 AFTER `four_months_ago`,
    ADD COLUMN `last_year` INT NOT NULL DEFAULT 0 AFTER `this_year`;
