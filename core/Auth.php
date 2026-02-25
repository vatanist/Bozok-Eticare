<?php
/**
 * V-Commerce - Merkezi Yetkilendirme Motoru (RBAC)
 */
class Auth
{
    private static $userPermissions = null;

    /**
     * Mevcut oturumdaki kullanıcının yetkisini kontrol eder.
     */
    public static function can($permissionSlug)
    {
        // Admin her şeyi yapabilir (Bypass)
        if (self::hasRole('admin')) {
            return true;
        }

        $permissions = self::getUserPermissions();
        return in_array($permissionSlug, $permissions);
    }

    /**
     * Kullanıcının rolünü kontrol eder.
     */
    public static function hasRole($roleSlug)
    {
        $user = currentUser();
        return ($user && isset($user['role']) && $user['role'] === $roleSlug);
    }

    /**
     * Kullanıcının sahip olduğu tüm yetki sluglarını döner.
     */
    public static function getUserPermissions()
    {
        if (self::$userPermissions !== null) {
            return self::$userPermissions;
        }

        $user = currentUser();
        if (!$user) {
            return [];
        }

        // Kullanıcının rolüne bağlı yetkileri DB'den çek
        $roleSlug = $user['role'];
        $rows = Database::fetchAll("
            SELECT p.slug 
            FROM permissions p
            JOIN role_permissions rp ON p.id = rp.permission_id
            JOIN roles r ON r.id = rp.role_id
            WHERE r.slug = ?
        ", [$roleSlug]);

        self::$userPermissions = array_column($rows, 'slug');
        return self::$userPermissions;
    }

    /**
     * Sistemi ilk kez kurarken temel yetkileri tanımlar.
     */
    public static function seed()
    {
        // Temel Yetkiler
        $perms = [
            ['Panel Erişimi', 'view_admin_panel'],
            ['Ürün Yönetimi', 'manage_products'],
            ['Sipariş Yönetimi', 'manage_orders'],
            ['Müşteri Yönetimi', 'manage_customers'],
            ['Ayarları Düzenle', 'manage_settings'],
            ['Modül Yönetimi', 'manage_extensions'],
            ['CMS Sayfa Yönetimi', 'manage_cms']
        ];

        foreach ($perms as $p) {
            Database::query("INSERT IGNORE INTO permissions (name, slug) VALUES (?, ?)", $p);
        }

        // Temel Rol: Admin (Tüm yetkiler bypass edilir zaten)
        Database::query("INSERT IGNORE INTO roles (name, slug) VALUES (?, ?)", ['Yönetici', 'admin']);

        // Temel Rol: Editör
        Database::query("INSERT IGNORE INTO roles (name, slug) VALUES (?, ?)", ['Editör', 'editor']);

        // Editör Yetkilerini Bağla
        $editorRoleId = Database::fetch("SELECT id FROM roles WHERE slug = 'editor'")['id'];
        $editorPerms = ['view_admin_panel', 'manage_products', 'manage_orders'];

        foreach ($editorPerms as $slug) {
            $pId = Database::fetch("SELECT id FROM permissions WHERE slug = ?", [$slug])['id'];
            Database::query("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)", [$editorRoleId, $pId]);
        }
    }
}
