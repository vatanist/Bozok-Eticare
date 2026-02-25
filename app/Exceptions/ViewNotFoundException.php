<?php
/**
 * Bozok E-Ticaret — ViewNotFoundException
 *
 * gorunum() şablon bulamadığında fırlatılır.
 * Router dispatch'te yakalanıp 404'e dönüştürülür.
 *
 * @package App\Exceptions
 */

namespace App\Exceptions;

class ViewNotFoundException extends \RuntimeException
{
    private string $template;

    public function __construct(string $template)
    {
        $this->template = $template;
        parent::__construct("Şablon bulunamadı: {$template}");
    }

    public function getTemplate(): string
    {
        return $this->template;
    }
}
