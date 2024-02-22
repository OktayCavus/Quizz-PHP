<?php
session_start();

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Functions
{
    private $lang;
    private $languageHeader;
    private $selectedLang;
    public function __construct()
    {
        $this->languageHeader = apache_request_headers();
        $this->selectedLang = $this->languageHeader['Accept-Language'];
        $this->lang = new Language($this->selectedLang);
    }

    public function response($content, $code, $message, $error, $status)
    {
        $islem["content"] = $content;
        $islem["code"] = $code;
        $error_message =  $this->lang->getMessage($error) == "Hata mesajı bulunamadı" ? null : $this->lang->getMessage($error);
        $islem["error"] = $error_message;
        $success_message =  $this->lang->getMessage($message) == "Hata mesajı bulunamadı" ? null : $this->lang->getMessage($message);
        $islem["message"] = $success_message;
        $islem["status"] = $status;

        $sonuc = json_encode($islem, JSON_UNESCAPED_UNICODE);
        echo $sonuc;
    }

    function safeOrNotControl($method, $key)
    {
        return isset($method[$key]) && !is_null($method[$key]) ? trim(htmlspecialchars($method[$key])) : null;
    }

    function check($module, $perm)
    {
        if (!isset($_SESSION["user"]) || !isset($_SESSION["user"]["permissions"])) {
            $this->response(null, 403, null, 'ERR_SESSION_NOT_STARTED', false);
            return false;
        }

        if (in_array($perm, $_SESSION["user"]["permissions"][$module])) {
            return true;
        } else {
            $this->response(null, 403, null, 'ERR_ACCESS_DENIED', false);
            return false;
        }
    }

    function headerRequest(?bool $isHasSession)
    {
        $requestHeader = apache_request_headers();
        if (!isset($requestHeader["Authorization"]) && $isHasSession) {
            $this->response(null, 401, null, 'ERR_MISSING_AUTHORIZATION_HEADER', false);
            return;
        }
        return array($requestHeader);
    }

    function verifyToken($token)
    {
        $sec_key = $_ENV["SECRET_KEY"];
        try {
            $decoded = JWT::decode($token, new Key($sec_key, 'HS256'));
            return $decoded->username;
        } catch (Exception $e) {
            $this->response(null, 408, null, $e->getMessage(), false);
            return false;
        }
    }
}
