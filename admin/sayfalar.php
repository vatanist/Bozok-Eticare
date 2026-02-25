<?php
/**
 * Admin - Eski Sayfalar Ucu (Uyumluluk)
 */
require_once __DIR__ . '/../config/config.php';
requireAdmin();
mesaj('cms', 'Sayfa yönetimi yeni CMS ekranına taşındı.', 'success');
git('/admin/cms-sayfalar.php');
