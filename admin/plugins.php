<?php
/**
 * Admin Eklentiler (Uyumluluk Yönlendirmesi)
 */
require_once __DIR__ . '/../config/config.php';
requireAdmin();

mesaj('moduller', 'Eklenti ekranı Modül Merkezi içine taşındı.', 'success');
git('/admin/moduller.php?sekme=kurulu');
