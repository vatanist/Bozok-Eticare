<?php
/**
 * Bozok E-Ticaret - Veritabanı Bağlantısı (PDO Singleton)
 * 
 * v2.0: .env desteği eklendi. Önce .env'den okur,
 * yoksa eski hardcoded değerlere düşer (geriye uyumluluk).
 */

class Database
{
    private static $instance = null;
    private $pdo;

    private $host;
    private $dbname;
    private $username;
    private $password;

    private function __construct()
    {
        // .env varsa oradan oku, yoksa eski değerlere düş (geriye uyumluluk)
        $this->host = function_exists('env') ? env('DB_HOST', 'localhost') : 'localhost';
        $this->dbname = function_exists('env') ? env('DB_NAME', 'vcommerce') : 'vcommerce';
        $this->username = function_exists('env') ? env('DB_USER', 'root') : 'root';
        $this->password = function_exists('env') ? env('DB_PASS', '') : '';

        $this->connect();
    }

    private function connect()
    {
        try {
            $this->pdo = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );
        } catch (PDOException $e) {
            if (function_exists('isDebug') && isDebug()) {
                die("Veritabanı bağlantı hatası: " . $e->getMessage());
            }
            die("Veritabanı bağlantı hatası. Lütfen .env ayarlarını kontrol edin.");
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        try {
            $this->pdo->query('SELECT 1');
        } catch (PDOException $e) {
            $this->connect();
        }
        return $this->pdo;
    }

    public static function reconnect()
    {
        $db = self::getInstance();
        $db->connect();
    }

    public static function query($sql, $params = [])
    {
        $pdo = self::getInstance()->getConnection();
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'server has gone away') !== false || $e->getCode() == 'HY000') {
                self::reconnect();
                $pdo = self::getInstance()->getConnection();
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                return $stmt;
            }
            throw $e;
        }
    }

    public static function fetch($sql, $params = [])
    {
        return self::query($sql, $params)->fetch();
    }

    public static function fetchAll($sql, $params = [])
    {
        return self::query($sql, $params)->fetchAll();
    }

    public static function lastInsertId()
    {
        return self::getInstance()->getConnection()->lastInsertId();
    }
}
