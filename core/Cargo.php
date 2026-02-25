<?php

/**
 * Cargo & Logistics Engine
 * Bozok E-Ticaret Enterprise Logistics Management
 */
class Cargo
{
    /**
     * Aktif kargo firmalarını getirir.
     */
    public static function getCarriers()
    {
        // Şimdilik statik, ileride veritabanından veya ayarlardan gelebilir
        return [
            'aras' => ['name' => 'Aras Kargo', 'url' => 'https://www.araskargo.com.tr/kargo-takip?kargo_takip_no='],
            'yurtici' => ['name' => 'Yurtiçi Kargo', 'url' => 'https://www.yurticikargo.com/tr/online-servisler/kargo-takip?code='],
            'mng' => ['name' => 'MNG Kargo', 'url' => 'https://www.mngkargo.com.tr/gonderitakip/'],
            'surat' => ['name' => 'Sürat Kargo', 'url' => 'https://www.suratkargo.com.tr/kargoweb/bireysel.aspx?kargono='],
            'ptt' => ['name' => 'PTT Kargo', 'url' => 'https://gonderitakip.ptt.gov.tr/Track/PttDegerli/']
        ];
    }

    /**
     * Takip URL'ini döner.
     */
    public static function getTrackingUrl($carrier, $trackingNumber)
    {
        $carriers = self::getCarriers();
        if (isset($carriers[$carrier])) {
            return $carriers[$carrier]['url'] . $trackingNumber;
        }
        return '#';
    }

    /**
     * Sepetin toplam desisini hesaplar.
     */
    public static function calculateTotalDesi($items)
    {
        $totalDesi = 0;
        foreach ($items as $item) {
            $product = Database::fetch("SELECT desi FROM products WHERE id = ?", [$item['product_id'] ?? 0]);
            $desi = $product ? (float) $product['desi'] : 1; // Varsayılan 1 desi
            $totalDesi += ($desi * (int) $item['quantity']);
        }
        return $totalDesi;
    }

    /**
     * Desiye göre kargo fiyatını hesaplar.
     */
    public static function getShippingPrice($totalDesi, $carrierName = 'default')
    {
        $rate = Database::fetch(
            "SELECT price FROM shipping_rates WHERE min_desi <= ? AND max_desi >= ? LIMIT 1",
            [$totalDesi, $totalDesi]
        );

        return $rate ? (float) $rate['price'] : (float) ayar_getir('delivery_fee', 50); // Bulunamazsa genel ayarı kullan
    }
}
