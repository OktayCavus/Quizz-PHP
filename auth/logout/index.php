<?php

require_once('../../includes/functions.php');
require_once('../../languages/language.php');

class LogoutHandler
{
    private $functions;
    private $lang;

    public function __construct()
    {
        $this->functions = new Functions();
        $this->lang = $this->getLanguage();
    }

    private function getLanguage()
    {
        $languageHeader = apache_request_headers();
        $selectedLang = $languageHeader['Accept-Language'];
        return new Language($selectedLang);
    }

    public function logout()
    {
        $this->clearSession();
        $this->respondLogoutSuccess();
    }

    private function clearSession()
    {
        session_unset();
        session_destroy();
    }

    private function respondLogoutSuccess()
    {
        $this->functions->response(null, 200, 'MESSAGE_LOGOUT_SUCCESSFUL', null, true);
    }
}

// Kullanım
$logoutHandler = new LogoutHandler();
$logoutHandler->logout();
