<?php

/**
 * Trendyol Marketplace Adapter
 */
class TrendyolAdapter extends Marketplace
{
    protected $marketplaceName = 'trendyol';
    private $apiUrl = 'https://api.trendyol.com/sapigw/suppliers/';

    /**
     * Trendyol'a ürün gönderir.
     */
    public function pushProduct($productId)
    {
        $product = Product::get($productId); // Product modelinden tam veriyi al
        if (!$product)
            return false;

        // Trendyol API Formatına Dönüştürme (Mock Implementation)
        $payload = [
            'items' => [
                [
                    'barcode' => $product['barcode'] ?: 'VC-' . $product['id'],
                    'title' => $product['name'],
                    'productMainId' => $product['id'],
                    'brandId' => 1, // Örn: V-Commerce Marka ID
                    'categoryId' => 1, // Örn: Kategori eşleştirme mantığı gelecek
                    'quantity' => $product['stock'],
                    'stockCode' => $product['sku'],
                    'listPrice' => (float) $product['price'],
                    'salePrice' => (float) ($product['discount_price'] ?: $product['price']),
                    'vatRate' => 20,
                    'description' => $product['description']
                ]
            ]
        ];

        // API Call (Mock)
        // $response = $this->client->post('products', $payload);
        $response = ['status' => 'success', 'batchRequestId' => 'TR-' . uniqid()]; // Mock

        $this->log('pushProduct', $payload, $response);
        $this->setMapping($productId, $response['batchRequestId'], 'success');

        return true;
    }

    /**
     * Trendyol'dan siparişleri çeker.
     */
    public function fetchOrders()
    {
        // Mock Implementation
        $mockOrders = [
            [
                'orderNumber' => 'TR-' . rand(100000, 999999),
                'customerName' => 'Trendyol Deneme',
                'totalPrice' => 150.00,
                'items' => [['productId' => 1, 'quantity' => 1]]
            ]
        ];

        $this->log('fetchOrders', 'query_params', $mockOrders);
        return $mockOrders;
    }

    /**
     * Stok senkronizasyonu yapar.
     */
    public function syncInventory($productId)
    {
        $product = Database::fetch("SELECT id, stock, price, discount_price FROM products WHERE id = ?", [$productId]);
        if (!$product)
            return false;

        $mapping = self::getMapping($productId, 'trendyol');
        if (!$mapping)
            return false;

        $payload = [
            'items' => [
                [
                    'barcode' => $mapping['remote_id'],
                    'quantity' => $product['stock'],
                    'salePrice' => (float) ($product['discount_price'] ?: $product['price'])
                ]
            ]
        ];

        // API Call (Mock)
        $response = ['status' => 'success'];

        $this->log('syncInventory', $payload, $response);
        return true;
    }
}
