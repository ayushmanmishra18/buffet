    -- =============================================================
    --  Registration Redesign — SQL Commands
    --  Run in phpMyAdmin SQL tab IN ORDER.
    -- =============================================================


    -- =============================================================
    --  STEP 1: Create the plans table
    -- =============================================================

    CREATE TABLE IF NOT EXISTS `plans` (
        `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `name`            VARCHAR(100)    NOT NULL,
        `description`     TEXT            NULL,
        `billing_type`    TINYINT UNSIGNED NOT NULL COMMENT '5=subscription, 10=commission',
        `price`           DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
        `commission_rate` DECIMAL(5,2)    NOT NULL DEFAULT 0.00,
        `trial_days`      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
        `features`        JSON            NULL,
        `is_featured`     TINYINT(1)      NOT NULL DEFAULT 0,
        `status`          TINYINT(1)      NOT NULL DEFAULT 1 COMMENT '1=active, 0=inactive',
        `sort`            INT UNSIGNED    NOT NULL DEFAULT 0,
        `created_at`      TIMESTAMP       NULL DEFAULT NULL,
        `updated_at`      TIMESTAMP       NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        INDEX `plans_status_index` (`status`),
        INDEX `plans_sort_index`   (`sort`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


    -- =============================================================
    --  STEP 2: Create the restaurant_applications table
    -- =============================================================

    CREATE TABLE IF NOT EXISTS `restaurant_applications` (
        `id`                            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id`                       BIGINT UNSIGNED NOT NULL,

        -- Business identity
        `business_name`                 VARCHAR(200)    NOT NULL,
        `business_type`                 VARCHAR(100)    NULL,
        `cuisine_type`                  VARCHAR(200)    NULL,
        `business_address`              LONGTEXT        NOT NULL,
        `city`                          VARCHAR(100)    NULL,
        `state`                         VARCHAR(100)    NULL,
        `country`                       VARCHAR(100)    NULL,
        `zip_code`                      VARCHAR(20)     NULL,
        `website`                       VARCHAR(255)    NULL,

        -- Legal / documents
        `business_registration_number`  VARCHAR(100)    NULL,
        `tax_id`                        VARCHAR(100)    NULL,
        `food_license_number`           VARCHAR(100)    NULL,

        -- Contact
        `owner_name`                    VARCHAR(200)    NOT NULL,
        `owner_phone`                   VARCHAR(30)     NOT NULL,
        `owner_email`                   VARCHAR(150)    NOT NULL,
        `business_phone`                VARCHAR(30)     NULL,

        -- Plan
        `plan_id`                       BIGINT UNSIGNED NULL,
        `billing_type`                  TINYINT UNSIGNED NULL COMMENT '5=subscription, 10=commission',

        -- Payment
        `payment_method`                VARCHAR(50)     NULL,
        `payment_transaction_id`        VARCHAR(200)    NULL,
        `amount_paid`                   DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
        `payment_status`                VARCHAR(30)     NOT NULL DEFAULT 'pending',

        -- Verification
        `status`                        TINYINT UNSIGNED NOT NULL DEFAULT 0
                                        COMMENT '0=pending, 1=approved, 2=rejected',
        `rejection_reason`              TEXT            NULL,
        `reviewed_by`                   BIGINT UNSIGNED NULL,
        `reviewed_at`                   TIMESTAMP       NULL DEFAULT NULL,

        -- Extra
        `notes`                         TEXT            NULL,
        `created_at`                    TIMESTAMP       NULL DEFAULT NULL,
        `updated_at`                    TIMESTAMP       NULL DEFAULT NULL,

        PRIMARY KEY (`id`),
        INDEX `ra_user_id_index`   (`user_id`),
        INDEX `ra_status_index`    (`status`),
        INDEX `ra_plan_id_index`   (`plan_id`),

        CONSTRAINT `ra_user_fk` FOREIGN KEY (`user_id`)
            REFERENCES `users`(`id`) ON DELETE CASCADE,
        CONSTRAINT `ra_plan_fk` FOREIGN KEY (`plan_id`)
            REFERENCES `plans`(`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


    -- =============================================================
    --  STEP 3: Add `status` column to users table
    --  (needed to deactivate restaurant owners until approved)
    --  Skip if the column already exists.
    -- =============================================================

    -- Check first:
    -- SHOW COLUMNS FROM `users` LIKE 'status';
    -- If empty, run:

    ALTER TABLE `users`
        ADD COLUMN IF NOT EXISTS `status` TINYINT UNSIGNED NOT NULL DEFAULT 1
        COMMENT '1=active, 0=inactive/pending' AFTER `password`;


    -- =============================================================
    --  STEP 4: Seed sample plans
    --  Edit prices/names to suit your business before running.
    -- =============================================================

    INSERT INTO `plans`
        (`name`, `description`, `billing_type`, `price`, `commission_rate`,
        `trial_days`, `features`, `is_featured`, `status`, `sort`, `created_at`, `updated_at`)
    VALUES
    (
        'Starter',
        'Perfect for small restaurants just getting started',
        5, -- subscription
        29.99, 0.00, 14,
        '["Up to 50 menu items", "Basic analytics", "QR code menu", "Email support", "14-day free trial"]',
        0, 1, 1, NOW(), NOW()
    ),
    (
        'Professional',
        'Best for growing hotels and restaurants',
        5, -- subscription
        79.99, 0.00, 7,
        '["Unlimited menu items", "Advanced analytics", "Priority support", "Custom QR code", "Reservation management", "7-day free trial"]',
        1, 1, 2, NOW(), NOW()   -- is_featured = 1 → shows Popular badge
    ),
    (
        'Commission',
        'No upfront cost — we only earn when you do',
        10, -- commission
        0.00, 12.00, 0,
        '["No monthly fee", "12% commission per order", "All features included", "Dedicated account manager"]',
        0, 1, 3, NOW(), NOW()
    );


    -- =============================================================
    --  STEP 5: Add Plan & Restaurant Applications to sidebar menu
    --
    --  Both go under "Promo & Communication" (parent_id = 16).
    --  Change 16 if your installation uses a different id —
    --  verify with: SELECT id, name FROM backend_menus WHERE name = 'promo_communication';
    -- =============================================================

    -- Plans menu item
    INSERT INTO `backend_menus`
        (`name`, `link`, `icon`, `parent_id`, `priority`, `status`, `created_at`, `updated_at`)
    SELECT 'plans', 'plan', 'fas fa-tags', 16, 67, 1, NOW(), NOW()
    WHERE NOT EXISTS (SELECT 1 FROM `backend_menus` WHERE `link` = 'plan');

    -- Restaurant Applications menu item
    INSERT INTO `backend_menus`
        (`name`, `link`, `icon`, `parent_id`, `priority`, `status`, `created_at`, `updated_at`)
    SELECT 'restaurant_applications', 'restaurant-application', 'fas fa-store', 16, 66, 1, NOW(), NOW()
    WHERE NOT EXISTS (SELECT 1 FROM `backend_menus` WHERE `link` = 'restaurant-application');


    -- =============================================================
    --  STEP 6: Add permissions for Plans and Applications
    -- =============================================================

    INSERT INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
    SELECT name, 'web', NOW(), NOW()
    FROM (
        SELECT 'plan'                        AS name UNION ALL
        SELECT 'plan_create'                 UNION ALL
        SELECT 'plan_edit'                   UNION ALL
        SELECT 'plan_delete'                 UNION ALL
        SELECT 'restaurant_application'      UNION ALL
        SELECT 'restaurant_application_edit'
    ) AS p
    WHERE NOT EXISTS (
        SELECT 1 FROM `permissions` px WHERE px.name = p.name AND px.guard_name = 'web'
    );


    -- =============================================================
    --  STEP 7: Grant new permissions to super-admin (role_id = 1)
    -- =============================================================

    INSERT INTO `role_has_permissions` (`permission_id`, `role_id`)
    SELECT p.id, 1
    FROM `permissions` p
    WHERE p.name IN (
        'plan', 'plan_create', 'plan_edit', 'plan_delete',
        'restaurant_application', 'restaurant_application_edit'
    )
    AND NOT EXISTS (
        SELECT 1 FROM `role_has_permissions` rhp
        WHERE rhp.permission_id = p.id AND rhp.role_id = 1
    );


    -- =============================================================
    --  STEP 8: Clear permission + view cache
    --  Run via terminal (preferred):
    --      php artisan permission:cache-reset
    --      php artisan view:clear
    --      php artisan cache:clear
    --
    --  Or visit: https://yourdomain.com/clear-app-cache
    -- =============================================================


    -- =============================================================
    --  VERIFICATION QUERIES
    -- =============================================================

    -- Check tables exist:
    SHOW TABLES LIKE 'plans';
    SHOW TABLES LIKE 'restaurant_applications';

    -- Check plans were seeded:
    SELECT id, name, billing_type, price, commission_rate, status FROM `plans`;

    -- Check sidebar menu items:
    SELECT id, name, link, parent_id, status FROM `backend_menus`
    WHERE `link` IN ('plan', 'restaurant-application', 'advertisement');

    -- Check permissions:
    SELECT id, name FROM `permissions`
    WHERE name LIKE 'plan%' OR name LIKE 'restaurant_application%'
    ORDER BY name;

    -- Check users table has status column:
    SHOW COLUMNS FROM `users` LIKE 'status';
