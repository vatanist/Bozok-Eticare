<?php
/**
 * Müşteri — Baskı Dosyası Yükleme
 * Siparişe bağlı veya bağımsız dosya yükleme
 */
require_once __DIR__ . '/../config/config.php';
requireLogin();

$userId = $_SESSION['user_id'];
$orderId = intval($_GET['order_id'] ?? 0);
$message = '';
$msgType = '';

// POST: Dosya yükleme
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'upload_file') {
        if (empty($_FILES['print_file']['name'])) {
            $message = 'Dosya seçmediniz.';
            $msgType = 'error';
        } else {
            $file = $_FILES['print_file'];

            // Güvenli baskı dosyası yükleme
            $allowed = [
                'application/pdf',
                'image/jpeg',
                'image/png',
                'image/tiff',
                'application/postscript',  // .eps, .ai
                'application/illustrator',
                'application/x-indesign',
            ];
            $maxSize = 100 * 1024 * 1024; // 100MB

            $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : null;
            $detectedMime = $finfo ? finfo_file($finfo, $file['tmp_name']) : mime_content_type($file['tmp_name']);
            if ($finfo)
                finfo_close($finfo);

            if ($file['size'] > $maxSize) {
                $message = 'Dosya boyutu 100MB sınırını aşıyor.';
                $msgType = 'error';
            } elseif (!in_array($detectedMime, $allowed)) {
                $message = 'Geçersiz dosya türü. Kabul edilen: PDF, JPG, PNG, TIFF, EPS, AI';
                $msgType = 'error';
            } else {
                // Web root DIŞINA kaydet
                $uploadDir = ROOT_PATH . DIRECTORY_SEPARATOR . 'private_uploads' . DIRECTORY_SEPARATOR . 'print' . DIRECTORY_SEPARATOR;
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                    // .htaccess ile erişimi engelle
                    file_put_contents($uploadDir . '.htaccess', "Deny from all\n");
                }

                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $safeFilename = 'u' . $userId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

                if (move_uploaded_file($file['tmp_name'], $uploadDir . $safeFilename)) {
                    try {
                        Database::query(
                            "INSERT INTO print_files (order_id, user_id, filename, original_name, file_size, mime_type, status) VALUES (?,?,?,?,?,?,?)",
                            [
                                $orderId ?: null,
                                $userId,
                                $safeFilename,
                                htmlspecialchars($file['name'], ENT_QUOTES),
                                $file['size'],
                                $detectedMime,
                                'pending'
                            ]
                        );
                        $message = '✅ Dosyanız başarıyla yüklendi. Ekibimiz inceleyecek ve size bilgi verecek.';
                        $msgType = 'success';
                    } catch (Exception $e) {
                        @unlink($uploadDir . $safeFilename);
                        $message = 'Veritabanı hatası. Tekrar deneyin.';
                        $msgType = 'error';
                    }
                } else {
                    $message = 'Dosya kaydedilemedi. Sunucu yöneticisiyle iletişime geçin.';
                    $msgType = 'error';
                }
            }
        }
    }

    if ($action === 'design_request') {
        $desc = trim($_POST['description'] ?? '');
        $refUrl = trim($_POST['reference_url'] ?? '');
        $productId = intval($_POST['product_id'] ?? 0);

        if (strlen($desc) < 20) {
            $message = 'Tasarım talebinizi lütfen daha ayrıntılı açıklayın (en az 20 karakter).';
            $msgType = 'error';
        } else {
            Database::query(
                "INSERT INTO design_requests (order_id, user_id, product_id, description, reference_url, status) VALUES (?,?,?,?,?,?)",
                [$orderId ?: null, $userId, $productId ?: null, $desc, $refUrl ?: null, 'new']
            );
            $message = '✅ Tasarım talebiniz alındı. Ekibimiz sizinle iletişime geçecek.';
            $msgType = 'success';
        }
    }
}

// Daha önce yüklenen dosyalar
$myFiles = [];
try {
    $myFiles = Database::fetchAll(
        "SELECT * FROM print_files WHERE user_id = ? ORDER BY uploaded_at DESC LIMIT 20",
        [$userId]
    );
} catch (Exception $e) {
}

// Kullanıcının aktif siparişleri (dosya bağlamak için)
$myOrders = [];
try {
    $myOrders = Database::fetchAll(
        "SELECT id, order_number, status, created_at FROM orders WHERE user_id = ? AND status NOT IN ('delivered','cancelled') ORDER BY id DESC LIMIT 10",
        [$userId]
    );
} catch (Exception $e) {
}

$pageTitle = 'Baskı Dosyası Yükle';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top:24px;padding-bottom:48px">
    <div class="breadcrumb">
        <a href="<?= BASE_URL ?>/client/">Hesabım</a>
        <span class="separator"><i class="fas fa-chevron-right"></i></span>
        <span class="current">Baskı Dosyası Yükle</span>
    </div>

    <h1 style="margin:16px 0 8px"><i class="fas fa-print" style="color:var(--primary)"></i> Baskı Dosyası &amp; Tasarım
    </h1>
    <p style="color:var(--gray);margin-bottom:24px">Baskı dosyanızı yükleyin veya tasarım hizmeti talep edin.</p>

    <?php if ($message): ?>
        <div class="alert alert-<?= $msgType === 'success' ? 'success' : 'danger' ?>" style="margin-bottom:20px">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:32px">

        <!-- Dosya Yükleme -->
        <div class="card" style="padding:24px">
            <h3><i class="fas fa-upload" style="color:var(--primary)"></i> Dosya Yükle</h3>
            <p style="color:var(--gray);font-size:.9rem;margin-bottom:16px">
                PDF, JPG, PNG, TIFF, EPS, AI formatları kabul edilmektedir. Maksimum boyut: 100MB.
            </p>
            <form method="POST" enctype="multipart/form-data">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="upload_file">

                <?php if (!empty($myOrders)): ?>
                    <div class="form-group" style="margin-bottom:12px">
                        <label>Siparişe Bağla (opsiyonel)</label>
                        <select name="order_id" class="form-control">
                            <option value="">Sipariş seçme</option>
                            <?php foreach ($myOrders as $o): ?>
                                <option value="<?= $o['id'] ?>" <?= $o['id'] == $orderId ? 'selected' : '' ?>>
                                    #
                                    <?= e($o['order_number']) ?> —
                                    <?= e($o['status']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="form-group" style="margin-bottom:16px">
                    <label>Dosya Seçin *</label>
                    <div id="dropZone"
                        style="border:2px dashed var(--primary);border-radius:10px;padding:32px;text-align:center;cursor:pointer;transition:background .2s"
                        onclick="document.getElementById('printFile').click()"
                        ondragover="event.preventDefault();this.style.background='#eff6ff'"
                        ondragleave="this.style.background=''" ondrop="handleDrop(event)">
                        <i class="fas fa-cloud-upload-alt"
                            style="font-size:2rem;color:var(--primary);margin-bottom:8px"></i>
                        <p style="margin:0;font-weight:600">Dosyayı buraya sürükleyin veya tıklayın</p>
                        <p style="margin:4px 0 0;font-size:.8rem;color:var(--gray)">PDF · JPG · PNG · TIFF · EPS · AI —
                            Max 100MB</p>
                        <span id="selectedFileName"
                            style="display:block;margin-top:10px;font-size:.85rem;color:var(--primary);font-weight:500"></span>
                    </div>
                    <input type="file" id="printFile" name="print_file" style="display:none"
                        accept=".pdf,.jpg,.jpeg,.png,.tiff,.tif,.eps,.ai" onchange="showFileName(this)">
                </div>

                <button type="submit" class="btn btn-primary btn-block" id="uploadBtn">
                    <i class="fas fa-upload"></i> Dosyayı Yükle
                </button>
            </form>
        </div>

        <!-- Tasarım Talebi -->
        <div class="card" style="padding:24px">
            <h3><i class="fas fa-paint-brush" style="color:#8b5cf6"></i> Tasarım Hizmeti Talep Et</h3>
            <p style="color:var(--gray);font-size:.9rem;margin-bottom:16px">
                Tasarım dosyanız yoksa ekibimiz sizin için tasarlayabilir. Ne istediğinizi anlatın.
            </p>
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="design_request">

                <?php if (!empty($myOrders)): ?>
                    <div class="form-group" style="margin-bottom:12px">
                        <label>Siparişe Bağla (opsiyonel)</label>
                        <select name="order_id" class="form-control">
                            <option value="">Sipariş seçme</option>
                            <?php foreach ($myOrders as $o): ?>
                                <option value="<?= $o['id'] ?>">#
                                    <?= e($o['order_number']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="form-group" style="margin-bottom:12px">
                    <label>Ne Tasarlanmasını İstiyorsunuz? *</label>
                    <textarea name="description" class="form-control" rows="5" required
                        placeholder="Örn: Logo, slogan, renkler, tarih bilgisi. Olabildiğince ayrıntılı açıklayın..."></textarea>
                </div>
                <div class="form-group" style="margin-bottom:16px">
                    <label>Referans Görsel URL (opsiyonel)</label>
                    <input type="url" name="reference_url" class="form-control" placeholder="https://...">
                </div>

                <button type="submit" class="btn" style="background:#8b5cf6;color:#fff;width:100%;padding:12px">
                    <i class="fas fa-paper-plane"></i> Talebi Gönder
                </button>
            </form>
        </div>
    </div>

    <!-- Yüklenen Dosyalar -->
    <?php if (!empty($myFiles)): ?>
        <div class="card" style="padding:24px">
            <h3><i class="fas fa-folder-open"></i> Yüklediğim Dosyalar</h3>
            <table style="width:100%;border-collapse:collapse;margin-top:12px">
                <thead>
                    <tr style="border-bottom:2px solid #e5e7eb;font-size:.85rem;text-align:left">
                        <th style="padding:8px">Dosya Adı</th>
                        <th style="padding:8px">Boyut</th>
                        <th style="padding:8px">Durum</th>
                        <th style="padding:8px">Yüklenme</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $statusColors = ['pending' => '#f59e0b', 'approved' => '#22c55e', 'rejected' => '#ef4444', 'used' => '#6b7280'];
                    $statusLabels = ['pending' => 'İnceleniyor', 'approved' => 'Onaylandı', 'rejected' => 'Reddedildi', 'used' => 'Kullanıldı'];
                    foreach ($myFiles as $f):
                        $color = $statusColors[$f['status']] ?? '#6b7280';
                        $label = $statusLabels[$f['status']] ?? $f['status'];
                        $sizeMb = round($f['file_size'] / 1048576, 2);
                        ?>
                        <tr style="border-bottom:1px solid #f1f5f9">
                            <td style="padding:8px;font-size:.9rem">
                                <i class="fas fa-file" style="color:var(--primary)"></i>
                                <?= e($f['original_name']) ?>
                            </td>
                            <td style="padding:8px;font-size:.85rem;color:var(--gray)">
                                <?= $sizeMb ?>MB
                            </td>
                            <td style="padding:8px">
                                <span
                                    style="background:<?= $color ?>22;color:<?= $color ?>;padding:3px 10px;border-radius:20px;font-size:.8rem;font-weight:600">
                                    <?= $label ?>
                                </span>
                            </td>
                            <td style="padding:8px;font-size:.82rem;color:var(--gray)">
                                <?= date('d.m.Y H:i', strtotime($f['uploaded_at'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
    function showFileName(input) {
        const name = input.files[0]?.name || '';
        document.getElementById('selectedFileName').textContent = name ? '📎 ' + name : '';
    }

    function handleDrop(e) {
        e.preventDefault();
        document.getElementById('dropZone').style.background = '';
        const files = e.dataTransfer.files;
        if (files.length) {
            const input = document.getElementById('printFile');
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(files[0]);
            input.files = dataTransfer.files;
            showFileName(input);
        }
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
