<?php gorunum('ust', ['sayfa_basligi' => $sayfa['title'] ?? 'Sayfa']); ?>

<div class="cms-page-container" style="padding:40px 0;">
    <div class="container" style="max-width:900px;">

        <!-- Breadcrumb -->
        <div style="margin-bottom:30px;">
            <ul
                style="display:flex; align-items:center; list-style:none; padding:0; margin:0; gap:10px; font-size:0.9rem; color:var(--gray);">
                <li><a href="<?= BASE_URL ?>/" style="color:var(--gray); text-decoration:none;">Ana Sayfa</a></li>
                <li><i class="fas fa-chevron-right" style="font-size:0.7rem;"></i></li>
                <li style="color:var(--dark); font-weight:700;">
                    <?= temiz($sayfa['title'] ?? '') ?>
                </li>
            </ul>
        </div>

        <!-- İçerik Alanı -->
        <article class="cms-article"
            style="background:#fff; border:1px solid #e5e7eb; border-radius:24px; padding:50px; box-shadow:0 10px 30px rgba(0,0,0,0.02);">
            <header style="margin-bottom:40px; border-bottom:1px solid #f3f4f6; padding-bottom:30px;">
                <h1 style="font-size:2.5rem; font-weight:800; color:var(--dark); margin:0; line-height:1.2;">
                    <?= temiz($sayfa['title'] ?? '') ?>
                </h1>
                <?php if (!empty($sayfa['updated_at'])): ?>
                    <div
                        style="margin-top:15px; color:var(--gray); font-size:0.85rem; display:flex; align-items:center; gap:8px;">
                        <i class="far fa-clock"></i> Son Güncelleme:
                        <?= date('d.m.Y', strtotime($sayfa['updated_at'])) ?>
                    </div>
                <?php endif; ?>
            </header>

            <div class="cms-content" style="font-size:1.1rem; line-height:1.8; color:#374151;">
                <?= $sayfa['content'] ?? '' ?>
            </div>
        </article>

    </div>
</div>

<style>
    .cms-content h2 {
        font-size: 1.8rem;
        font-weight: 800;
        color: var(--dark);
        margin: 40px 0 20px;
    }

    .cms-content h3 {
        font-size: 1.4rem;
        font-weight: 700;
        color: var(--dark);
        margin: 30px 0 15px;
    }

    .cms-content p {
        margin-bottom: 20px;
    }

    .cms-content ul,
    .cms-content ol {
        padding-left: 25px;
        margin-bottom: 25px;
    }

    .cms-content li {
        margin-bottom: 10px;
    }

    .cms-content img {
        max-width: 100%;
        height: auto;
        border-radius: 12px;
        margin: 30px 0;
    }

    .cms-content blockquote {
        border-left: 5px solid var(--primary);
        background: var(--gray-50);
        padding: 25px 35px;
        margin: 35px 0;
        border-radius: 0 15px 15px 0;
        font-style: italic;
        color: var(--dark-600);
    }

    .cms-content table {
        width: 100%;
        border-collapse: collapse;
        margin: 30px 0;
    }

    .cms-content th {
        background: var(--gray-50);
        padding: 15px;
        text-align: left;
        border: 1px solid #e5e7eb;
        font-weight: 700;
    }

    .cms-content td {
        padding: 15px;
        border: 1px solid #e5e7eb;
    }

    .cms-content a {
        color: var(--primary);
        text-decoration: none;
        font-weight: 600;
        border-bottom: 2px solid var(--primary-light);
        transition: 0.3s;
    }

    .cms-content a:hover {
        background: var(--primary-light);
        color: var(--primary);
    }
</style>

<?php gorunum('alt'); ?>
