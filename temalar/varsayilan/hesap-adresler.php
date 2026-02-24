<?php gorunum('ust', ['sayfa_basligi' => 'Adreslerim']); ?>

<div class="client-addresses" style="padding:40px 0;">
    <div class="client-layout" style="display:grid; grid-template-columns: 280px 1fr; gap:40px;">

        <!-- Sidebar -->
        <div class="client-sidebar-container">
            <?php gorunum('hesap-sidebar', ['aktif_sayfa' => 'addresses', 'kullanici' => $kullanici]); ?>
        </div>

        <!-- İçerik -->
        <div class="client-main-content">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
                <div>
                    <h1 style="font-weight:800; margin-bottom:10px;">Adreslerim</h1>
                    <p style="color:var(--gray);">Siparişlerinin teslim edileceği adresleri buradan yönetebilirsin.</p>
                </div>
                <button onclick="document.getElementById('addForm').style.display='block'; this.style.display='none';"
                    class="buton"
                    style="background:var(--primary); color:#fff; border:none; padding:12px 25px; border-radius:30px; font-weight:700; cursor:pointer; box-shadow:0 4px 15px rgba(26,86,219,0.2);">
                    <i class="fas fa-plus"></i> Yeni Adres Ekle
                </button>
            </div>

            <?php mesaj_goster('address'); ?>

            <!-- Yeni Adres Formu -->
            <div id="addForm"
                style="display:none; background:#fff; border:1px solid var(--primary); border-radius:16px; padding:30px; margin-bottom:30px; box-shadow:0 10px 25px rgba(26,86,219,0.05);">
                <h3 style="font-weight:800; margin-bottom:20px; color:var(--primary);">Yeni Adres Bilgileri</h3>
                <form action="<?= BASE_URL ?>/client/addresses.php" method="POST">
                    <input type="hidden" name="action" value="add">
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px;">
                        <div>
                            <label style="display:block; margin-bottom:8px; font-weight:600; font-size:0.9rem;">Adres
                                Başlığı (Ev, İş vb.)</label>
                            <input type="text" name="title" placeholder="Örn: Evim"
                                style="width:100%; padding:12px; border:1px solid #e5e7eb; border-radius:8px; outline:none;"
                                required>
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:8px; font-weight:600; font-size:0.9rem;">Telefon
                                Numarası</label>
                            <input type="tel" name="phone" placeholder="05XX XXX XX XX"
                                style="width:100%; padding:12px; border:1px solid #e5e7eb; border-radius:8px; outline:none;"
                                required>
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px;">
                        <div>
                            <label style="display:block; margin-bottom:8px; font-weight:600; font-size:0.9rem;">Alıcı
                                Adı</label>
                            <input type="text" name="first_name" value="<?= temiz($kullanici['first_name']) ?>"
                                style="width:100%; padding:12px; border:1px solid #e5e7eb; border-radius:8px; outline:none;"
                                required>
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:8px; font-weight:600; font-size:0.9rem;">Alıcı
                                Soyadı</label>
                            <input type="text" name="last_name" value="<?= temiz($kullanici['last_name']) ?>"
                                style="width:100%; padding:12px; border:1px solid #e5e7eb; border-radius:8px; outline:none;"
                                required>
                        </div>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:20px; margin-bottom:20px;">
                        <div>
                            <label
                                style="display:block; margin-bottom:8px; font-weight:600; font-size:0.9rem;">Şehir</label>
                            <select name="city" id="addCity"
                                style="width:100%; padding:12px; border:1px solid #e5e7eb; border-radius:8px; outline:none;"
                                required>
                                <option value="">Şehir Seçiniz</option>
                            </select>
                        </div>
                        <div>
                            <label
                                style="display:block; margin-bottom:8px; font-weight:600; font-size:0.9rem;">İlçe</label>
                            <select name="district" id="addDistrict"
                                style="width:100%; padding:12px; border:1px solid #e5e7eb; border-radius:8px; outline:none;"
                                required>
                                <option value="">İlçe Seçiniz</option>
                            </select>
                        </div>
                        <div>
                            <label
                                style="display:block; margin-bottom:8px; font-weight:600; font-size:0.9rem;">Mahalle</label>
                            <input type="text" name="neighborhood"
                                style="width:100%; padding:12px; border:1px solid #e5e7eb; border-radius:8px; outline:none;">
                        </div>
                    </div>
                    <div style="margin-bottom:25px;">
                        <label style="display:block; margin-bottom:8px; font-weight:600; font-size:0.9rem;">Açık
                            Adres</label>
                        <textarea name="address_line" placeholder="Cadde, sokak, bina ve kapı no..."
                            style="width:100%; padding:12px; border:1px solid #e5e7eb; border-radius:8px; outline:none; min-height:100px;"
                            required></textarea>
                    </div>
                    <div style="display:flex; justify-content:flex-end; gap:15px;">
                        <button type="button"
                            onclick="document.getElementById('addForm').style.display='none'; document.querySelector('.buton[onclick*=\'addForm\']').style.display='block';"
                            style="background:none; border:none; color:var(--gray); cursor:pointer; font-weight:600;">İptal</button>
                        <button type="submit" class="buton"
                            style="background:var(--primary); color:#fff; border:none; padding:12px 30px; border-radius:30px; font-weight:700; cursor:pointer;">Adresi
                            Kaydet</button>
                    </div>
                </form>
            </div>

            <!-- Adres Listesi -->
            <div style="display:grid; grid-template-columns: repeat(2, 1fr); gap:20px;">
                <?php if (empty($adresler)): ?>
                    <div
                        style="grid-column: span 2; text-align:center; padding:50px; background:#fff; border:1px dashed #e5e7eb; border-radius:16px;">
                        <i class="fas fa-map-marked-alt fa-3x"
                            style="color:var(--gray-light); margin-bottom:15px; display:block;"></i>
                        <p style="color:var(--gray);">Henüz bir adres tanımlamadınız.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($adresler as $idx => $a): ?>
                        <div style="background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:25px; transition:0.3s; position:relative;"
                            onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='#e5e7eb'">
                            <div
                                style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:15px;">
                                <h4
                                    style="margin:0; font-weight:800; color:var(--dark); display:flex; align-items:center; gap:8px;">
                                    <i class="fas fa-home" style="color:var(--primary-light);"></i>
                                    <?= temiz($a['title']) ?>
                                    <?php if ($a['is_default']): ?>
                                        <span
                                            style="font-size:0.65rem; background:var(--success-light); color:var(--success); padding:3px 8px; border-radius:10px;">Varsayılan</span>
                                    <?php endif; ?>
                                </h4>
                                <div style="display:flex; gap:10px;">
                                    <button onclick="document.getElementById('editForm_<?= $idx ?>').style.display='block'"
                                        style="background:none; border:none; color:var(--gray); cursor:pointer; font-size:0.9rem;"
                                        title="Düzenle"><i class="fas fa-edit"></i></button>
                                    <form action="<?= BASE_URL ?>/client/addresses.php" method="POST"
                                        onsubmit="return confirm('Bu adresi silmek istediğinize emin misiniz?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="address_id" value="<?= $a['id'] ?>">
                                        <button type="submit"
                                            style="background:none; border:none; color:var(--danger); cursor:pointer; font-size:0.9rem;"
                                            title="Sil"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                            <div style="color:var(--dark-600); font-size:0.9rem; font-weight:700; margin-bottom:5px;">
                                <?= temiz($a['first_name'] . ' ' . $a['last_name']) ?> <span
                                    style="font-weight:400; color:var(--gray); margin-left:10px;">
                                    <?= temiz($a['phone']) ?>
                                </span>
                            </div>
                            <div style="color:var(--gray); font-size:0.85rem; line-height:1.6;">
                                <?= temiz($a['address_line']) ?><br>
                                <?= temiz(($a['neighborhood'] ? $a['neighborhood'] . ' Mah. ' : '') . $a['district'] . ' / ' . $a['city']) ?>
                            </div>

                            <!-- Düzenleme Formu (Hidden Modal Like) -->
                            <div id="editForm_<?= $idx ?>"
                                style="display:none; position:absolute; top:0; left:0; width:100%; height:100%; background:#fff; z-index:10; padding:20px; border-radius:16px;">
                                <h4
                                    style="margin-top:0; font-weight:800; border-bottom:1px solid #f3f4f6; padding-bottom:10px;">
                                    Adresi Düzenle</h4>
                                <form action="<?= BASE_URL ?>/client/addresses.php" method="POST" style="font-size:0.8rem;">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="address_id" value="<?= $a['id'] ?>">
                                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:10px;">
                                        <input type="text" name="title" value="<?= temiz($a['title']) ?>" placeholder="Başlık"
                                            style="width:100%; padding:8px; border:1px solid #e5e7eb; border-radius:6px; outline:none;"
                                            required>
                                        <input type="tel" name="phone" value="<?= temiz($a['phone']) ?>" placeholder="Telefon"
                                            style="width:100%; padding:8px; border:1px solid #e5e7eb; border-radius:6px; outline:none;"
                                            required>
                                    </div>
                                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:10px;">
                                        <select name="city" id="editCity_<?= $idx ?>"
                                            style="width:100%; padding:8px; border:1px solid #e5e7eb; border-radius:6px;">
                                            <option value="">Şehir</option>
                                        </select>
                                        <select name="district" id="editDistrict_<?= $idx ?>"
                                            style="width:100%; padding:8px; border:1px solid #e5e7eb; border-radius:6px;">
                                            <option value="">İlçe</option>
                                        </select>
                                    </div>
                                    <textarea name="address_line"
                                        style="width:100%; padding:8px; border:1px solid #e5e7eb; border-radius:6px; min-height:60px; margin-bottom:10px;"
                                        required><?= temiz($a['address_line']) ?></textarea>
                                    <div style="display:flex; justify-content:flex-end; gap:10px;">
                                        <button type="button"
                                            onclick="document.getElementById('editForm_<?= $idx ?>').style.display='none'"
                                            style="background:none; border:none; color:var(--gray); cursor:pointer; font-size:0.75rem;">Vazgeç</button>
                                        <button type="submit" class="buton"
                                            style="background:var(--primary); color:#fff; padding:6px 15px; border-radius:6px; font-size:0.75rem;">Güncelle</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<script src="<?= BASE_URL ?>/js/address-selector.js"></script>
<script>
    // Add selector
    initAddressSelector('addCity', 'addDistrict');

    // Edit selectors
    <?php foreach ($adresler as $idx => $a): ?>
            initAddressSelector('editCity_<?= $idx ?>', 'editDistrict_<?= $idx ?>', {
                city: '<?= $a['city'] ?>',
                district: '<?= $a['district'] ?>'
            });
    <?php endforeach; ?>
</script>

<?php gorunum('alt'); ?>
