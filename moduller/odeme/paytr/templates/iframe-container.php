<?php
/**
 * PayTR Modülü - Iframe Şablonu
 */
?>
<div class="paytr-iframe-container"
    style="min-height: 600px; background: #fff; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; padding: 10px;">
    <script src="https://www.paytr.com/js/iframeResizer.min.js"></script>
    <iframe src="https://www.paytr.com/odeme/guvenli/<?= $token ?>" id="paytriframe" frameborder="0" scrolling="no"
        style="width: 100%;"></iframe>
    <script>iFrameResize({}, '#paytriframe');</script>
</div>