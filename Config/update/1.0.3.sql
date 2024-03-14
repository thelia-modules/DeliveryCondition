
# This is a fix for InnoDB in MySQL >= 4.1.x
# It "suspends judgement" for fkey relationships until are tables are set.
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- delivery_weight_condition
-- ---------------------------------------------------------------------

DROP TABLE IF EXISTS `delivery_weight_condition`;

CREATE TABLE `delivery_weight_condition`
(
    `id` INTEGER NOT NULL AUTO_INCREMENT,
    `delivery_module_id` INTEGER NOT NULL,
    `min_weight` DECIMAL NOT NULL,
    `max_weight` DECIMAL NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `fi_delivery_weight_condition_delivery_module_id` (`delivery_module_id`),
    CONSTRAINT `fk_delivery_weight_condition_delivery_module_id`
        FOREIGN KEY (`delivery_module_id`)
            REFERENCES `module` (`id`)
            ON UPDATE RESTRICT
            ON DELETE CASCADE
) ENGINE=InnoDB;

# This restores the fkey checks, after having unset them earlier
SET FOREIGN_KEY_CHECKS = 1;
