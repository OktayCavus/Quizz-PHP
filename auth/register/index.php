<?php

require_once('../../baglanti.php');

header("Content-Type: application/json; charset=utf-8");

function response($content, $code, $mesaj)
{
    $islem["content"] = $content;
    $islem["code"] = $code;

    if ($code == 200) {
        $islem["success"] = true;
    } else {
        $islem["success"] = false;

        switch ($code) {
            case 400:
                $islem["error"] = "Geçersiz istek. Lütfen tüm zorunlu alanları doldurun.";
                break;
            case 401:
                $islem["error"] = "Kullanıcı adı ve şifre zorunlu alanlardır.";
                break;
            case 402:
                $islem["error"] = "Şifre en az 6 karakter olmalıdır.";
                break;
            case 403:
                $islem["error"] = "Geçersiz e-posta adresi.";
                break;
            default:
                $islem["error"] = "Bilinmeyen bir hata oluştu.";
        }
    }

    $sonuc = json_encode($islem, JSON_UNESCAPED_UNICODE);
    echo $sonuc;
}

function safeOrNotControl($method, $key)
{
    return isset($method[$key]) && !is_null($method[$key]) ? trim(htmlspecialchars($method[$key])) : null;
}

if ($_POST) {
    $islem = array();
    $username = safeOrNotControl($_POST, 'username');
    $password = safeOrNotControl($_POST, 'password');
    $firstname = safeOrNotControl($_POST, 'firstname');
    $lastname = safeOrNotControl($_POST, 'lastname');
    $email = safeOrNotControl($_POST, 'email');
    $phonenumber = safeOrNotControl($_POST, 'phonenumber');

    if ($username && $password && $firstname && $lastname && $email) {
        if (strlen($password) >= 6) {
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                response(NULL, 403, "Geçersiz e-posta adresi.");
            } else {
                try {
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $password = sha1(md5($password));
                    $sorgu = $pdo->prepare("INSERT INTO students (username, password, first_name, last_name, email, phone_number) VALUES (?, ?, ?, ?, ?, ?)");
                    $sorgu->execute([$username, $password, $firstname, $lastname, $email, $phonenumber]);

                    if ($sorgu->rowCount() > 0) {
                        unset($_POST['password']);
                        response($_POST, 200, "Kayıt eklendi");
                    } else {
                        response(NULL, 201, "Kayıt Eklenemedi");
                    }
                } catch (PDOException $e) {
                    echo 'Veri eklenirken hata oluştu: ' . $e->getMessage();
                }
            }
        } else {
            response(NULL, 402, "Şifre en az 6 karakter olmalıdır.");
        }
    } else {
        response(NULL, 400, "Zorunlu alanları doldurun: username, password, firstname, lastname");
    }
}
