<?php
class Language
{
    private $lang;
    private $available_langs = array('tr', 'en');

    public function __construct($lang)
    {
        $this->loadLanguageFile($lang);
        $_SESSION['lang'] = 'tr';
        $this->check();
    }

    private function check()
    {
        if (isset($_GET['lang']) && $_GET['lang'] != '') {
            if (in_array($_GET['lang'], $this->available_langs)) {
                $_SESSION['lang'] = $_GET['lang'];
            }
        }
    }

    private function loadLanguageFile($lang)
    {
        $language_file = __DIR__ . "/{$lang}/languages_{$lang}.json";
        if (file_exists($language_file)) {
            $langContent = file_get_contents($language_file);
            $langContent = json_decode($langContent, true);

            if ($langContent !== null) {
                $this->lang = $langContent;
            } else {
                echo "Hata: Dil dosyası geçersiz JSON formatına sahip.";
                exit;
            }
        } else {
            echo "Dil dosyası bulunamadı: {$language_file}";
            exit;
        }
    }

    public function getMessage($key)
    {
        if (isset($this->lang[$key])) {
            return $this->lang[$key];
        } else {
            return "Hata mesajı bulunamadı";
        }
    }
}
