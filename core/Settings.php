<?php
/**
 * Bozok E-Ticaret - Merkezi Ayar Motoru (Settings Engine)
 */
class Settings
{
    private static $cache = [];

    /**
     * Bir ayar değerini getirir.
     */
    public static function get($name, $group = 'general', $default = null)
    {
        $cacheKey = "{$group}.{$name}";
        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $row = Database::fetch("SELECT value, type FROM settings WHERE group_key = ? AND name = ?", [$group, $name]);

        if (!$row) {
            return $default;
        }

        $value = self::cast($row['value'], $row['type']);
        self::$cache[$cacheKey] = $value;

        return $value;
    }

    /**
     * Bir ayar grubunu toplu olarak getirir.
     */
    public static function group($group)
    {
        $rows = Database::fetchAll("SELECT name, value, type FROM settings WHERE group_key = ?", [$group]);
        $groupData = [];

        foreach ($rows as $row) {
            $groupData[$row['name']] = self::cast($row['value'], $row['type']);
            self::$cache["{$group}.{$row['name']}"] = $groupData[$row['name']];
        }

        return $groupData;
    }

    /**
     * Ayar kaydeder veya günceller.
     */
    public static function set($name, $value, $group = 'general', $type = null)
    {
        // Tip otomatik algıla (Eğer belirtilmemişse)
        if ($type === null) {
            if (is_array($value) || is_object($value))
                $type = 'json';
            elseif (is_bool($value))
                $type = 'bool';
            elseif (is_numeric($value))
                $type = 'int';
            else
                $type = 'text';
        }

        $dbValue = ($type === 'json') ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value;

        // Kayıt var mı kontrol et
        $exists = Database::fetch("SELECT id FROM settings WHERE group_key = ? AND name = ?", [$group, $name]);

        if ($exists) {
            Database::query(
                "UPDATE settings SET value = ?, type = ? WHERE group_key = ? AND name = ?",
                [$dbValue, $type, $group, $name]
            );
        } else {
            Database::query(
                "INSERT INTO settings (group_key, name, value, type) VALUES (?, ?, ?, ?)",
                [$group, $name, $dbValue, $type]
            );
        }

        // Cache temizle
        self::$cache["{$group}.{$name}"] = $value;
    }

    /**
     * Veriyi tipine göre dönüştürür.
     */
    private static function cast($value, $type)
    {
        switch ($type) {
            case 'json':
                return json_decode($value ?? '[]', true);
            case 'bool':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'int':
                return (int) $value;
            default:
                return (string) $value;
        }
    }
}
