<?php
session_start();

use Firebase\JWT\JWT;
// ! bu da decode için
use Firebase\JWT\Key;

class Functions
{
    function response($content, $code, $mesaj, $error, $success)
    {
        $islem["content"] = $content;
        $islem["code"] = $code;
        $islem["error"] = $error;
        $islem["mesaj"] = $mesaj;
        $islem["success"] = $success;

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

            $_SESSION["user"]["error"] = "Oturum açmadınız veya oturumunuz sonlandırılmış.";
            $this->response(null, 403, null, $_SESSION["user"]["error"], false);
            return false;
        }

        if (in_array($perm, $_SESSION["user"]["permissions"][$module])) {
            return true;
        } else {
            $_SESSION["user"]["error"] = "Erişim izni yok.";
            $this->response(null, 403, null, $_SESSION["user"]["error"], false);
            return false;
        }
    }

    function headerRequest()
    {
        $requestHeader = apache_request_headers();
        if (!isset($requestHeader["Authorization"])) {
            $this->response(null, 401, null, "Yetkisiz Erişim (authorization header eksik)", false);
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
