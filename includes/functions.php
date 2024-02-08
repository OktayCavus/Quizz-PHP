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
    $valid = isset($_SESSION["user"]);
    if ($valid) {
        $valid = in_array($perm, $_SESSION["user"]["permissions"][$module]);
    }
    if ($valid) {
        return true;
    } else {
        $_SESSION["user"]["error"] = "Erişim izni yok.";
        response(null, 403, null, $_SESSION["user"]["error"], false);
        return false;
    }
}
