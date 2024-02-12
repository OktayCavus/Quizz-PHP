<?php
session_start();

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
        response(null, 403, null, $_SESSION["user"]["error"], false);
        return false;
    }

    if (in_array($perm, $_SESSION["user"]["permissions"][$module])) {
        return true;
    } else {
        $_SESSION["user"]["error"] = "Erişim izni yok.";
        response(null, 403, null, $_SESSION["user"]["error"], false);
        return false;
    }
}
