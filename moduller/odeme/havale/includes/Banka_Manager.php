<?php
/**
 * Havale Modülü - Banka Hesap Yöneticisi
 */
class Banka_Manager
{

    public function get_banka_listesi()
    {
        // İleride veritabanına bağlanabilir, şimdilik sabit ama profesyonel dizi
        return [
            [
                'ad' => 'Ziraat Bankası',
                'alici' => 'Bozok E-Ticaret Ltd. Şti.',
                'sube' => 'İstanbul Kurumsal',
                'hesap_no' => '12345678',
                'iban' => 'TR00 0000 0000 0000 1234 5678 90',
                'logo' => 'ziraat.png'
            ],
            [
                'ad' => 'Garanti BBVA',
                'alici' => 'Bozok E-Ticaret Ltd. Şti.',
                'sube' => 'Maslak Ticari',
                'hesap_no' => '98765432',
                'iban' => 'TR11 1111 1111 1111 9876 5432 10',
                'logo' => 'garanti.png'
            ]
        ];
    }
}
