<?php

require_once('../../config/baglanti.php');
require_once "../../includes/functions.php";

header("Content-Type: application/json; charset=utf-8");
$pdo->beginTransaction();



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
                response(NULL, 403, NULL, "Geçersiz e-posta error", false);
            } else {
                $checkUsername = $pdo->prepare("Select * From students Where username = ? ");
                $checkUsername->execute([$username]);
                if ($checkUsername->rowCount() > 0) {
                    response(null, 400, null, "Kullanıcı adı kullanılıyor.", false);
                } else {
                    $checkEmail = $pdo->prepare("Select * From students Where email = ?");
                    $checkEmail->execute([$email]);
                    if ($checkEmail->rowCount() > 0) {
                        response(null, 400, null, "Bu E-posta adresi kullanılıyor.", false);
                    } else {
                        try {
                            $password = sha1(md5($password));
                            $sorgu = $pdo->prepare("INSERT INTO students (username, password, first_name, last_name, email, phone_number) VALUES (?, ?, ?, ?, ?, ?)");
                            $sorgu->execute([$username, $password, $firstname, $lastname, $email, $phonenumber]);

                            if ($sorgu->rowCount() > 0) {
                                unset($_POST['password']);
                                response($_POST, 200, "Kayıt eklendi", NULL, true);
                            } else {
                                response(NULL, 201, NULL, "Kayıt Eklenemedi", false);
                            }
                        } catch (PDOException $e) {
                            response(null, 500, null, "Sunucu Hatası", false);
                            echo 'Veri eklenirken hata oluştu: ' . $e->getMessage();
                        }
                    }
                }
            }
        } else {
            response(NULL, 402, NULL, "Şifre en az 6 karakter olmalıdır.", false);
        }
    } else {
        response(NULL, 400, NULL, "Zorunlu alanları doldurun: username, password, firstname, lastname", false);
    }
    $pdo->commit();
}
