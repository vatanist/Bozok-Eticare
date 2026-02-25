<?php
/**
 * Bozok E-Ticaret — PageController
 *
 * Kurumsal CMS sayfa görüntüleme.
 *
 * @package App\Controllers
 */

namespace App\Controllers;

class PageController extends BaseController
{
    // ===================== BAŞLANGIÇ: CMS İÇERİK GÜVENLİK =====================
    private function icerikTemizle(string $icerik): string
    {
        $izinli_etiketler = '<p><a><strong><em><b><i><u><ul><ol><li><h1><h2><h3><h4><blockquote><br><hr><table><thead><tbody><tr><th><td><img>';
        return strip_tags($icerik, $izinli_etiketler);
    }
    // ===================== BİTİŞ: CMS İÇERİK GÜVENLİK =====================

    /**
     * Kurumsal CMS sayfa görünümü
     */
    public function show(string $slug): void
    {
        $sayfa = \Database::fetch(
            "SELECT * FROM cms_pages WHERE slug = ? AND durum = 'yayinda' LIMIT 1",
            [$slug]
        );

        if (!$sayfa) {
            \Router::notFound();
            return;
        }

        $sayfa['icerik_guvenli'] = $this->icerikTemizle((string) ($sayfa['icerik'] ?? ''));

        $this->view($sayfa['sablon'] ?: 'sayfa', [
            'sayfa_basligi' => !empty($sayfa['meta_title']) ? $sayfa['meta_title'] : $sayfa['title'],
            'sayfa' => $sayfa,
            'meta_desc' => !empty($sayfa['meta_description'])
                ? $sayfa['meta_description']
                : mb_substr(strip_tags($sayfa['icerik_guvenli']), 0, 160),
            'canonical_url' => !empty($sayfa['canonical_url'])
                ? $sayfa['canonical_url']
                : url('sayfa/' . $sayfa['slug']),
        ]);
    }
}
