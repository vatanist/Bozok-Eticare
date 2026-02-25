<?php
/**
 * Bozok E-Ticaret - Bozkurt Core (Çekirdek Fonksiyonlar)
 * 
 * Sadece çekirdek fonksiyonları içerir:
 * - PHP 8 Polyfill'ler
 * - Global ayarlar ($bozkurt)
 * - Yönlendirme (git)
 * - Tema sistemi (gorunum, tema_linki, tema_yolu)
 * - Hook/Kanca sistemi
 * - Modül sistemi
 * - Flash mesaj sistemi
 * - CSRF güvenliği
 *
 * Ürün, sepet, auth, medya fonksiyonları → app/Helpers/ altına taşındı.
 * Bootstrap/app.php tarafından otomatik yüklenir.
 *
 * @package VCommerce
 * @version 2.0.0
 */

// ==================== PHP 8 POLYFILLS ====================
if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle)
    {
        return (string) $needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}
if (!function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle)
    {
        return $needle !== '' && substr($haystack, -strlen($needle)) === (string) $needle;
    }
}
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle)
    {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}

// ==================== ANA AYARLAR ====================

/**
 * Bozkurt Yönetim Dizisi
 * Site genelindeki tema ve modül ayarlarını tutar.
 */
$bozkurt = [
    'tema_adi' => 'varsayilan',
    'modul_yolu' => __DIR__ . '/../moduller',
    'tema_yolu' => __DIR__ . '/../temalar',
    'render_tema_adi' => null,
    'surum' => '2.0.0'
];

// ===================== BAŞLANGIÇ: TEMA ÇÖZÜMLEME =====================
/**
 * Tema adını geriye uyumluluk kuralları ile çözümler.
 */
function tema_adi_cozumle($tema_adi)
{
    $tema_adi = trim((string) $tema_adi);

    if (class_exists('TemaSozlesmesi')) {
        return TemaSozlesmesi::temaAdiCozumle($tema_adi);
    }

    $takma_adlar = [
        'shoptimizer' => 'svs-tema',
    ];

    return $takma_adlar[$tema_adi] ?? $tema_adi;
}

/**
 * Aktif tema adını güvenli şekilde günceller.
 */
function aktif_tema_ayarla($tema_adi)
{
    global $bozkurt;

    $tema_adi = tema_adi_cozumle($tema_adi ?: 'varsayilan');
    $tema_klasor = $bozkurt['tema_yolu'] . '/' . $tema_adi;

    if (!is_dir($tema_klasor)) {
        $tema_adi = 'varsayilan';
        $tema_klasor = $bozkurt['tema_yolu'] . '/varsayilan';
    }

    // ===================== BAŞLANGIÇ: TEMA SÖZLEŞMESİ FAIL-OPEN =====================
    if (class_exists('TemaSozlesmesi')) {
        $dogrulama = TemaSozlesmesi::temaSozlesmesiniDogrula($tema_klasor);
        if (!($dogrulama['gecerli'] ?? false)) {
            // Runtime'da siteyi ayakta tutmak için varsayılan temaya düş.
            $tema_adi = 'varsayilan';
        }
    }
    // ===================== BİTİŞ: TEMA SÖZLEŞMESİ FAIL-OPEN =====================

    $bozkurt['tema_adi'] = $tema_adi;
    return $bozkurt['tema_adi'];
}
// ===================== BİTİŞ: TEMA ÇÖZÜMLEME =====================

// ==================== ÇEKİRDEK ARAÇLAR ====================

/**
 * Başka Bir Sayfaya Yönlendirir
 */
function git($adres)
{
    if (str_starts_with($adres, 'http')) {
        header("Location: " . $adres);
    } else {
        $baseUrl = rtrim(BASE_URL, '/');
        $path = '/' . ltrim($adres, '/');
        header("Location: " . $baseUrl . $path);
    }
    exit;
}

/**
 * Router uyumlu URL üretir.
 */
function url($yol = '')
{
    $base = rtrim(BASE_URL, '/');
    $yol = '/' . ltrim((string) $yol, '/');
    return $base . $yol;
}

// ==================== TEMA SİSTEMİ ====================

/**
 * Tema/görünüm girdisinde yasak karakter var mı kontrol eder.
 */
function tema_gorunum_yasak_icerir($deger): bool
{
    return str_contains((string) $deger, '..')
        || str_contains((string) $deger, "\0")
        || str_contains((string) $deger, ':');
}

/**
 * Tema görünüm yolu doğrular.
 */
function tema_gorunum_girdisi_dogrula($deger): bool
{
    return is_string($deger) && preg_match('#^[a-zA-Z0-9_\-/]+$#', $deger) === 1;
}

/**
 * Tema Şablonu Yükler (View)
 * Örn: gorunum('urun-detay', ['urun' => $veri]);
 *
 * Güvenlik: Path traversal koruması (.., \0 engeli)
 * Hata: Şablon bulunamazsa ViewNotFoundException fırlatır
 *
 * @throws \App\Exceptions\ViewNotFoundException
 */
function gorunum($yol, $veriler = [])
{
    global $bozkurt;

    // ===================== BAŞLANGIÇ: GÖRÜNÜM GİRDİ GÜVENLİĞİ =====================
    if (tema_gorunum_yasak_icerir($yol) || !tema_gorunum_girdisi_dogrula((string) $yol)) {
        throw new \App\Exceptions\ViewNotFoundException((string) $yol);
    }
    // ===================== BİTİŞ: GÖRÜNÜM GİRDİ GÜVENLİĞİ =====================

    if (!empty($veriler)) {
        extract($veriler, EXTR_SKIP);
    }

    // Otomatik SEO meta değişkenleri
    $meta_title = $veriler['sayfa_basligi'] ?? ayar_getir('site_title', 'Bozok E-Ticaret');
    $meta_desc = $veriler['meta_desc'] ?? '';

    // ===================== BAŞLANGIÇ: TEMA GÖRÜNÜM FALLBACK =====================
    $aktif_sablon = $bozkurt['tema_yolu'] . '/' . $bozkurt['tema_adi'] . '/' . $yol . '.php';
    $varsayilan_sablon = $bozkurt['tema_yolu'] . '/varsayilan/' . $yol . '.php';

    if (is_file($aktif_sablon)) {
        $bozkurt['render_tema_adi'] = (string) $bozkurt['tema_adi'];
        try {
            require $aktif_sablon;
        } finally {
            $bozkurt['render_tema_adi'] = null;
        }
        return;
    }

    if (is_file($varsayilan_sablon)) {
        $bozkurt['render_tema_adi'] = 'varsayilan';
        try {
            require $varsayilan_sablon;
        } finally {
            $bozkurt['render_tema_adi'] = null;
        }
        return;
    }
    // ===================== BİTİŞ: TEMA GÖRÜNÜM FALLBACK =====================

    throw new \App\Exceptions\ViewNotFoundException($yol);
}

/**
 * Belirtilen tema klasöründen görünüm yükler.
 * Örn: gorunum_tema('admin-temalar', $veriler, 'varsayilan')
 *
 * @throws \App\Exceptions\ViewNotFoundException
 */
function gorunum_tema($yol, $veriler = [], $tema_adi = 'varsayilan')
{
    global $bozkurt;

    // ===================== BAŞLANGIÇ: TEMA BAZLI GÖRÜNÜM GÜVENLİĞİ =====================
    $tema_adi = tema_adi_cozumle((string) $tema_adi ?: 'varsayilan');

    if (tema_gorunum_yasak_icerir($yol) || tema_gorunum_yasak_icerir($tema_adi)) {
        error_log('Gorunum tema güvenlik doğrulaması başarısız.');
        throw new \App\Exceptions\ViewNotFoundException((string) $yol);
    }

    if (!tema_gorunum_girdisi_dogrula((string) $yol) || !preg_match('#^[a-zA-Z0-9_-]+$#', (string) $tema_adi)) {
        error_log('Gorunum tema regex doğrulaması başarısız.');
        throw new \App\Exceptions\ViewNotFoundException((string) $yol);
    }
    // ===================== BİTİŞ: TEMA BAZLI GÖRÜNÜM GÜVENLİĞİ =====================

    if (!empty($veriler)) {
        extract($veriler, EXTR_SKIP);
    }

    $meta_title = $veriler['sayfa_basligi'] ?? ayar_getir('site_title', 'Bozok E-Ticaret');
    $meta_desc = $veriler['meta_desc'] ?? '';

    $sablon = $bozkurt['tema_yolu'] . '/' . $tema_adi . '/' . $yol . '.php';
    if (is_file($sablon)) {
        $bozkurt['render_tema_adi'] = (string) $tema_adi;
        try {
            require $sablon;
        } finally {
            $bozkurt['render_tema_adi'] = null;
        }
        return;
    }

    error_log('Gorunum tema bulunamadı: ' . $tema_adi . '/' . $yol);
    throw new \App\Exceptions\ViewNotFoundException($yol);
}

/**
 * Admin görünümlerini temadan bağımsız yükler.
 */
function gorunum_admin($yol, $veriler = [])
{
    if (tema_gorunum_yasak_icerir($yol) || !preg_match('#^[a-zA-Z0-9_\-/]+$#', (string) $yol)) {
        throw new \App\Exceptions\ViewNotFoundException((string) $yol);
    }

    if (!empty($veriler)) {
        extract($veriler, EXTR_SKIP);
    }

    $base = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__) . DIRECTORY_SEPARATOR;
    $sablon = $base . 'admin' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $yol . '.php';

    if (is_file($sablon)) {
        require $sablon;
        return;
    }

    error_log('Admin görünüm bulunamadı: ' . $yol);
    throw new \App\Exceptions\ViewNotFoundException((string) $yol);
}

/**
 * Tema Dosyalarının (CSS/JS) Linkini Verir
 */
function tema_linki($dosya = '')
{
    global $bozkurt;
    $baseUrl = rtrim(BASE_URL, '/');
    $kullanilan_tema = $bozkurt['render_tema_adi'] ?: $bozkurt['tema_adi'];
    return $baseUrl . '/temalar/' . $kullanilan_tema . '/' . ltrim($dosya, '/');
}

// ==================== MODÜL & KANCA (HOOK) SİSTEMİ ====================

/**
 * Kancalar Dizisi
 */
$bozkurt_hooks = [];
$bozkurt_scripts = [];
$bozkurt_styles = [];

/**
 * Dosya Başlığındaki Bilgileri Okur (WP/Shoptimizer Standartı)
 * @param string $dosya Dosya yolu
 * @param array $headers Okunacak başlıklar (Örn: ['Name' => 'Plugin Name'])
 */
function dosya_bilgisi_oku($dosya, $headers)
{
    if (!file_exists($dosya))
        return [];

    $icerik = file_get_contents($dosya, false, null, 0, 4096);
    $bilgi = [];

    foreach ($headers as $anahtar => $baslik) {
        if (preg_match('/' . preg_quote($baslik, '/') . ':(.*)$/mi', $icerik, $match)) {
            $bilgi[$anahtar] = trim($match[1]);
        } else {
            $bilgi[$anahtar] = '';
        }
    }
    return $bilgi;
}

/**
 * Bir Kancaya Fonksiyon Bağlar (Hook Ekle)
 * @param string $ad Kanca adı (örn: 'footer_basi')
 * @param callable $fonksiyon Çalıştırılacak fonksiyon
 * @param int $oncelik Çalışma sırası (küçük olan önce çalışır)
 */
function hook_ekle($ad, $fonksiyon, $oncelik = 10)
{
    global $bozkurt_hooks;
    $bozkurt_hooks[$ad][$oncelik][] = $fonksiyon;
}

/**
 * Bir Kancanın Tanımlı Olup Olmadığını Kontrol Eder
 */
function hook_ekli_mi($ad)
{
    global $bozkurt_hooks;
    return isset($bozkurt_hooks[$ad]) && !empty($bozkurt_hooks[$ad]);
}

/**
 * Bir Kancayı Tetikler (Hook Çalıştır)
 * @param string $ad Tetiklenecek kanca adı
 * @param mixed $veriler Fonksiyonlara gönderilecek veri (opsiyonel)
 * @return mixed Filtrelenmiş veya işlenmiş veri
 */
function hook_calistir($ad, $veriler = null)
{
    global $bozkurt_hooks;

    // ===================== BAŞLANGIÇ: KANCA ADI ÇÖZÜMLEME =====================
    if (class_exists('TemaSozlesmesi')) {
        $ad = TemaSozlesmesi::kancaAdiCozumle($ad);
    }
    // ===================== BİTİŞ: KANCA ADI ÇÖZÜMLEME =====================

    if (!isset($bozkurt_hooks[$ad])) {
        return $veriler;
    }

    // Öncelik sırasına göre diz (1, 10, 100...)
    ksort($bozkurt_hooks[$ad]);

    foreach ($bozkurt_hooks[$ad] as $oncelik => $fonksiyonlar) {
        foreach ($fonksiyonlar as $fonksiyon) {
            if (is_callable($fonksiyon)) {
                $sonuc = call_user_func($fonksiyon, $veriler);
                if ($sonuc !== null) {
                    $veriler = $sonuc; // Filtre mantığı
                }
            }
        }
    }

    return $veriler;
}

/**
 * Sisteme Script Enjekte Eder
 */
function script_ekle($kod_veya_dosya)
{
    global $bozkurt_scripts;
    $bozkurt_scripts[] = $kod_veya_dosya;
}

/**
 * Sisteme Stil Enjekte Eder
 */
function stil_ekle($kod_veya_dosya)
{
    global $bozkurt_styles;
    $bozkurt_styles[] = $kod_veya_dosya;
}

/**
 * Modül Dosya Yolunu Döndürür (Internal)
 */
function modul_yolu($kategori, $kod, $dosya = '')
{
    global $bozkurt;
    return $bozkurt['modul_yolu'] . '/' . $kategori . '/' . $kod . ($dosya ? '/' . $dosya : '');
}

/**
 * Modül URL'sini Döndürür (External Asset Access)
 */
function modul_linki($kategori, $kod, $dosya = '')
{
    return BASE_URL . '/moduller/' . $kategori . '/' . $kod . ($dosya ? '/' . $dosya : '');
}

/**
 * Tema Dosya Yolunu Döndürür
 */
function tema_yolu($tema = '', $dosya = '')
{
    global $bozkurt;
    $tema = $tema ?: ($bozkurt['render_tema_adi'] ?: $bozkurt['tema_adi']);
    return $bozkurt['tema_yolu'] . '/' . $tema . ($dosya ? '/' . $dosya : '');
}

/**
 * Bir Modülü Sayfaya Yükler (Doğrudan Çağrı)
 */
function modul_yukle($tur, $kod, $ayarlar = [])
{
    global $bozkurt;
    $dosya = modul_yolu($tur, $kod, 'init.php');

    if (file_exists($dosya)) {
        if (!empty($ayarlar)) {
            extract($ayarlar);
        }
        require $dosya;
    } else {
        echo "<!-- Modul Bulunamadı: $tur/$kod -->";
    }
}

// ==================== MESAJ SİSTEMİ (FLASH) ====================

/**
 * Tek Seferlik Mesaj Oluşturur
 */
function mesaj($anahtar, $metin = null, $tip = 'success')
{
    if ($metin === null) {
        $m = $_SESSION['flash'][$anahtar] ?? null;
        if ($m) {
            unset($_SESSION['flash'][$anahtar]);
            return $m;
        }
        return null;
    }
    $_SESSION['flash'][$anahtar] = ['metin' => $metin, 'tip' => $tip];
}

/**
 * Tek Seferlik Mesajı Ekranda Gösterir
 */
function mesaj_goster($anahtar)
{
    $m = mesaj($anahtar);
    if ($m) {
        $tip = ($m['tip'] == 'error') ? 'danger' : $m['tip'];
        echo '<div class="alert alert-' . $tip . '">';
        echo temiz($m['metin']);
        echo '</div>';
    }
}

// ==================== GÜVENLİK (CSRF) ====================

function csrf_kod()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

function dogrula_csrf()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $istek_token = $_POST['csrf_token'] ?? '';
        $oturum_token = $_SESSION['csrf_token'] ?? '';

        if (!is_string($istek_token) || !is_string($oturum_token) || !hash_equals($oturum_token, $istek_token)) {
            die("Güvenlik hatası: CSRF doğrulaması başarısız!");
        }
    }
}

// ==================== UYUMLULUK ALIAS'LARI ====================
// Bu fonksiyonlar çekirdek fonksiyonların alias'larıdır.
// Helper dosyalarındaki alias'lar bootstrap/app.php üzerinden yüklenir.

/** @deprecated v2.0'da kaldırılacak. gorunum() kullanın. */
function view($y, $v)
{
    return gorunum($y, $v);
}

/** @deprecated v2.0'da kaldırılacak. mesaj() kullanın. */
function flash($a, $m, $t = 'success')
{
    return mesaj($a, $m, $t);
}

/** @deprecated v2.0'da kaldırılacak. git() kullanın. */
function redirect($a)
{
    return git($a);
}

/** @deprecated v2.0'da kaldırılacak. mesaj_goster() kullanın. */
function showFlash($a)
{
    return mesaj_goster($a);
}
