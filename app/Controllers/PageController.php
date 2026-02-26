<?php
/**
 * V-Commerce — PageController
 *
 * CMS statik sayfa görüntüleme.
 *
 * @package App\Controllers
 */

namespace App\Controllers;

class PageController extends BaseController
{
    /**
     * Statik sayfa görünümü
     *
     * @param string $slug  Sayfa slug'ı
     */
    public function show(string $slug): void
    {
        $sayfa = \Database::fetch(
            "SELECT * FROM pages WHERE slug = ? AND status = 1",
            [$slug]
        );

        if (!$sayfa) {
            \Router::notFound();
            return;
        }

        $this->view('sayfa', [
            'sayfa_basligi' => !empty($sayfa['meta_title']) ? $sayfa['meta_title'] : $sayfa['title'],
            'sayfa' => $sayfa,
            'meta_description' => !empty($sayfa['meta_description'])
                ? $sayfa['meta_description']
                : mb_substr(strip_tags($sayfa['content']), 0, 160)
        ]);
    }
}
