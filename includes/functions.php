<?php

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
