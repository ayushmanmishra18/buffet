-- Restaurant Payments menu + permission (admin + restaurant owner).
-- Run once in phpMyAdmin. Safe to re-run (guarded by NOT EXISTS).

-- 1. Sidebar menu under Accounts (parent_id 21)
INSERT INTO `backend_menus`
    (`name`, `link`, `icon`, `parent_id`, `priority`, `status`, `created_at`, `updated_at`)
SELECT 'restaurant_payment', 'restaurant-payment', 'fas fa-credit-card', 21, 80, 1, NOW(), NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `backend_menus` WHERE `link` = 'restaurant-payment'
);

-- 2. Permission
INSERT INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
SELECT name, guard_name, NOW(), NOW()
FROM (SELECT 'restaurant-payment' AS name, 'web' AS guard_name) AS new_perms
WHERE NOT EXISTS (
    SELECT 1 FROM `permissions` p
    WHERE p.name = new_perms.name AND p.guard_name = new_perms.guard_name
);

-- 3. Grant to Admin (role 1) and Restaurant Owner (role 3)
INSERT INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id
FROM `permissions` p
JOIN `roles` r ON r.id IN (1, 3)
WHERE p.name = 'restaurant-payment' AND p.guard_name = 'web'
AND NOT EXISTS (
    SELECT 1 FROM `role_has_permissions` rhp
    WHERE rhp.permission_id = p.id AND rhp.role_id = r.id
);

-- 4. Clear Spatie permission cache afterwards:
--      php artisan permission:cache-reset
