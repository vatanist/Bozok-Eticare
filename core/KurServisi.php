<?php
/**
 * Bozok E-Ticaret Kur Servisi
 */
class KurServisi
{
    // ===================== BAŞLANGIÇ: TCMB KUR SERVİSİ =====================
    /**
     * TCMB USD ve EUR satış kurlarını getirir.
     */
    public static function tcmbKurlariGetir(): array
    {
        $varsayilan = [
            'date' => date('d.m.Y'),
            'USD' => 1.0,
            'EUR' => 1.0,
        ];

        $xmlUrl = 'https://www.tcmb.gov.tr/kurlar/today.xml';
        $icerik = @file_get_contents($xmlUrl);
        if ($icerik === false) {
            return $varsayilan;
        }

        $xml = @simplexml_load_string($icerik);
        if (!$xml) {
            return $varsayilan;
        }

        $tarih = (string) ($xml['Date'] ?? date('d.m.Y'));
        $sonuc = [
            'date' => $tarih,
            'USD' => 1.0,
            'EUR' => 1.0,
        ];

        foreach ($xml->Currency as $kur) {
            $kod = (string) ($kur['CurrencyCode'] ?? '');
            if (!in_array($kod, ['USD', 'EUR'], true)) {
                continue;
            }

            $deger = (string) ($kur->BanknoteSelling ?? $kur->ForexSelling ?? '1');
            $deger = (float) str_replace(',', '.', $deger);
            if ($deger > 0) {
                $sonuc[$kod] = $deger;
            }
        }

        return $sonuc;
    }
    // ===================== BİTİŞ: TCMB KUR SERVİSİ =====================
}

if (!function_exists('getTCMBRates')) {
    /**
     * Geriye uyumluluk sarmalayıcısı.
     */
    function getTCMBRates(): array
    {
        return KurServisi::tcmbKurlariGetir();
    }
}
