<?php gorunum('ust', ['sayfa_basligi' => $sayfa['title']]); ?>

<article class="shoptimizer-page-content"
    style="max-width: 900px; margin: 40px auto; background: #fff; border-radius: 30px; border: 1px solid #f1f5f9; padding: 60px;">

    <header style="text-align: center; margin-bottom: 50px;">
        <h1
            style="font-size: 3rem; font-weight: 950; color: var(--dark); margin: 0; letter-spacing: -2px; line-height: 1;">
            <?= temiz($sayfa['title']) ?>
        </h1>
        <div style="width: 60px; height: 5px; background: var(--primary); margin: 30px auto; border-radius: 10px;">
        </div>
    </header>

    <div class="content-body" style="line-height: 1.8; color: #334155; font-size: 1.15rem;">
        <?= $sayfa['content'] ?>
    </div>

</article>

<?php gorunum('alt'); ?>