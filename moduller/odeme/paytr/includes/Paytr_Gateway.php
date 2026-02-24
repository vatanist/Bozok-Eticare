<?php
/**
 * PayTR Modülü - Profesyonel Gateway Sınıfı
 */
class Paytr_Gateway
{

    private $merchant_id;
    private $merchant_key;
    private $merchant_salt;
    private $test_mode;

    public function __construct()
    {
        $this->merchant_id = ayar_getir('paytr_merchant_id');
        $this->merchant_key = ayar_getir('paytr_merchant_key');
        $this->merchant_salt = ayar_getir('paytr_merchant_salt');
        $this->test_mode = ayar_getir('paytr_test_mode', '1');
    }

    /**
     * Ödeme Token'ı Alır
     */
    public function get_payment_token($order, $user)
    {
        if (!$this->merchant_id || !$this->merchant_key || !$this->merchant_salt) {
            return ['success' => false, 'error' => 'PayTR ayarları yapılandırılmamış.'];
        }

        $payment_amount = intval($order['total'] * 100);
        $merchant_oid = $order['order_number'] . '_' . time();
        $user_basket = base64_encode(json_encode([[$order['order_number'] . ' Nolu Sipariş', number_format($order['total'], 2, '.', ''), 1]]));

        $ip = $_SERVER["REMOTE_ADDR"];
        $hash_str = $this->merchant_id . $ip . $merchant_oid . $user['email'] . $payment_amount . $user_basket . '00' . 'TL' . $this->test_mode;
        $paytr_token = base64_encode(hash_hmac('sha256', $hash_str . $this->merchant_salt, $this->merchant_key, true));

        $post_vals = [
            'merchant_id' => $this->merchant_id,
            'user_ip' => $ip,
            'merchant_oid' => $merchant_oid,
            'email' => $user['email'],
            'payment_amount' => $payment_amount,
            'paytr_token' => $paytr_token,
            'user_basket' => $user_basket,
            'debug_on' => 1,
            'no_installment' => 0,
            'max_installment' => 0,
            'user_name' => $user['first_name'] . ' ' . $user['last_name'],
            'user_address' => $order['shipping_address'],
            'user_phone' => $order['shipping_phone'] ?: '905555555555',
            'merchant_ok_url' => BASE_URL . '/modul-isleyici.php?modul=paytr&sayfa=success&id=' . $order['id'],
            'merchant_fail_url' => BASE_URL . '/modul-isleyici.php?modul=paytr&sayfa=fail&id=' . $order['id'],
            'timeout_limit' => "30",
            'currency' => "TL",
            'test_mode' => $this->test_mode
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.paytr.com/odeme/api/get-token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vals);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $result = curl_exec($ch);
        curl_close($ch);

        $res = json_decode($result, 1);
        return ($res['status'] == 'success') ? ['success' => true, 'token' => $res['token']] : ['success' => false, 'error' => $res['reason']];
    }
}
