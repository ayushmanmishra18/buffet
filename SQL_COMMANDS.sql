-- =============================================================
--  Advertisement / Banner Blocks Feature
--  SQL Commands — run these in phpMyAdmin (Query tab)
--  Run them IN ORDER, one block at a time.
-- =============================================================


-- =============================================================
--  STEP 1: Create the advertisements table
--
--  Run this if you are NOT using Laravel's artisan migrate.
--  If you CAN run `php artisan migrate` on the server, skip
--  this block and just run the artisan command instead.
-- =============================================================

CREATE TABLE IF NOT EXISTS `advertisements` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title`        VARCHAR(200)    NOT NULL,
    `description`  TEXT            NULL,
    `link`         VARCHAR(500)    NULL,
    `position`     VARCHAR(30)     NOT NULL DEFAULT 'middle'
                   COMMENT 'top | after_hero | middle | bottom',
    `sort`         INT UNSIGNED    NOT NULL DEFAULT 0,
    `status`       TINYINT UNSIGNED NOT NULL DEFAULT 5
                   COMMENT '5 = active, 10 = inactive',
    `creator_id`   BIGINT UNSIGNED NULL,
    `creator_type` VARCHAR(255)    NULL,
    `editor_id`    BIGINT UNSIGNED NULL,
    `editor_type`  VARCHAR(255)    NULL,
    `created_at`   TIMESTAMP       NULL DEFAULT NULL,
    `updated_at`   TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `advertisements_position_index` (`position`),
    INDEX `advertisements_status_index`   (`status`),
    INDEX `advertisements_sort_index`     (`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =============================================================
--  STEP 2: Add "Advertisements" to the admin sidebar menu
--
--  FIRST: Run this query to find the correct parent_id for
--  the "Promo & Communication" section on YOUR installation:
--
--      SELECT id, name FROM backend_menus
--      WHERE name = 'promo_communication';
--
--  Then replace the number 16 below with the id you get back.
--  (On a standard fresh install it is always 16.)
-- =============================================================

-- 2a. Verify the parent first (run this SELECT, note the id):
SELECT id, name, link FROM `backend_menus` WHERE `name` = 'promo_communication';

-- 2b. Insert the Advertisements menu item (edit parent_id if yours differs from 16):
INSERT INTO `backend_menus`
    (`name`, `link`, `icon`, `parent_id`, `priority`, `status`, `created_at`, `updated_at`)
SELECT
    'advertisements',
    'advertisement',
    'fas fa-rectangle-ad',
    COALESCE(
        (SELECT `id` FROM `backend_menus` WHERE `name` = 'promo_communication' AND `parent_id` = 0 LIMIT 1),
        16   -- fallback to default seeder value
    ),
    68,
    1,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `backend_menus` WHERE `link` = 'advertisement'
);

-- 2c. Confirm it was inserted:
SELECT id, name, link, parent_id, status FROM `backend_menus` WHERE `link` = 'advertisement';


-- =============================================================
--  STEP 3: Add permissions for the advertisement module
--
--  This inserts CRUD permissions so you can assign them to
--  roles in Admin → Role management.
--
--  Adjust the `guard_name` value if your app uses a different
--  guard (check your existing permissions rows to confirm).
-- =============================================================

INSERT INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
SELECT name, guard_name, NOW(), NOW()
FROM (
    SELECT 'advertisement'        AS name, 'web' AS guard_name UNION ALL
    SELECT 'advertisement_create' AS name, 'web' AS guard_name UNION ALL
    SELECT 'advertisement_edit'   AS name, 'web' AS guard_name UNION ALL
    SELECT 'advertisement_delete' AS name, 'web' AS guard_name
) AS new_perms
WHERE NOT EXISTS (
    SELECT 1 FROM `permissions` p
    WHERE p.name = new_perms.name AND p.guard_name = new_perms.guard_name
);


-- =============================================================
--  STEP 4: Grant all advertisement permissions to the
--          Super Admin role
--
--  Finds the super-admin role (role_id = 1 is standard for
--  this app) and links all 4 new permissions to it.
-- =============================================================

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, 1          -- change 1 to your super-admin role id if different
FROM `permissions` p
WHERE p.name IN (
    'advertisement',
    'advertisement_create',
    'advertisement_edit',
    'advertisement_delete'
)
AND NOT EXISTS (
    SELECT 1
    FROM `role_has_permissions` rhp
    WHERE rhp.permission_id = p.id
      AND rhp.role_id = 1
);


-- =============================================================
--  STEP 5: Clear the permission cache
--
--  Spatie permissions are cached. After inserting new rows
--  the cache must be cleared so the app picks them up.
--
--  Option A (preferred): run in terminal on the server:
--      php artisan permission:cache-reset
--
--  Option B: truncate the cache table (if using DB cache):
-- =============================================================

-- Option B — only run this if your CACHE_DRIVER=database
-- TRUNCATE TABLE `cache`;


-- =============================================================
--  STEP 6: (Optional) Insert sample advertisement rows
--
--  Uncomment and edit to seed test data directly from SQL.
--  Images must be uploaded via the admin panel — these rows
--  will show the default placeholder image until then.
-- =============================================================

/*
INSERT INTO `advertisements`
    (`title`, `description`, `link`, `position`, `sort`, `status`, `created_at`, `updated_at`)
VALUES
    ('Summer Sale',      'Up to 50% off on selected items', 'https://example.com/sale',   'top',        1, 5, NOW(), NOW()),
    ('Free Delivery',    'Order above $20 get free delivery', NULL,                        'after_hero', 2, 5, NOW(), NOW()),
    ('New Arrivals',     'Check out our latest menu items',  'https://example.com/new',   'middle',     3, 5, NOW(), NOW()),
    ('Download our App', 'Get exclusive app-only discounts', 'https://example.com/app',   'bottom',     4, 5, NOW(), NOW());
*/


-- =============================================================
--  VERIFICATION QUERIES
--  Run these after the steps above to confirm everything is set.
-- =============================================================

-- Check the table was created:
SHOW TABLES LIKE 'advertisements';

-- Check the menu entry:
SELECT id, name, link, parent_id, status
FROM `backend_menus`
WHERE link = 'advertisement';

-- Check the permissions:
SELECT id, name, guard_name
FROM `permissions`
WHERE name LIKE 'advertisement%'
ORDER BY name;

-- Check role assignments:
SELECT r.name AS role, p.name AS permission
FROM `role_has_permissions` rhp
JOIN `permissions`  p ON p.id = rhp.permission_id
JOIN `roles`        r ON r.id = rhp.role_id
WHERE p.name LIKE 'advertisement%'
ORDER BY r.name, p.name;
