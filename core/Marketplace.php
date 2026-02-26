<?php

/**
 * Marketplace Master Engine
 * V-Commerce Enterprise Omnichannel Management
 */
abstract class Marketplace
{
    protected $marketplaceName;
    protected $config;

    public function __construct($config = [])
    {
        $this->config = $config;
    }

    /**
     * Pazaryerine ürün gönderir veya günceller.
     */
    abstract public function pushProduct($productId);

    /**
     * Pazaryerinden siparişleri çeker.
     */
    abstract public function fetchOrders();

    /**
     * Stok ve fiyat senkronizasyonu yapar.
     */
    abstract public function syncInventory($productId);

    /**
     * Log kaydı oluşturur.
     */
    protected function log($action, $request, $response, $status = 'success')
    {
        Database::query(
            "INSERT INTO marketplace_logs (marketplace, action, request_data, response_data, status) VALUES (?, ?, ?, ?, ?)",
            [
                $this->marketplaceName,
                $action,
                is_array($request) ? json_encode($request) : $request,
                is_array($response) ? json_encode($response) : $response,
                $status
            ]
        );
    }

    /**
     * Ürün eşleşmesini kaydeder veya günceller.
     */
    protected function setMapping($productId, $remoteId, $status = 'synced')
    {
        $exists = Database::fetch(
            "SELECT id FROM product_mappings WHERE product_id = ? AND marketplace = ?",
            [$productId, $this->marketplaceName]
        );

        if ($exists) {
            Database::query(
                "UPDATE product_mappings SET remote_id = ?, sync_status = ?, last_sync = NOW() WHERE id = ?",
                [$remoteId, $status, $exists['id']]
            );
        } else {
            Database::query(
                "INSERT INTO product_mappings (product_id, marketplace, remote_id, sync_status, last_sync) VALUES (?, ?, ?, ?, NOW())",
                [$productId, $this->marketplaceName, $remoteId, $status]
            );
        }
    }

    /**
     * Ürün eşleşmesini getirir.
     */
    public static function getMapping($productId, $marketplace)
    {
        return Database::fetch(
            "SELECT * FROM product_mappings WHERE product_id = ? AND marketplace = ?",
            [$productId, $marketplace]
        );
    }
}
