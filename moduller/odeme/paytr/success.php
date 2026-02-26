// Config zaten modul-isleyici.php tarafından yüklendi
requireLogin();

$orderId = intval($_GET['id'] ?? 0);
$veriler = [
'sayfa_basligi' => 'Ödeme Başarılı',
'basarili' => true,
'order_id' => $orderId
];

gorunum('odeme-sonuc', $veriler);