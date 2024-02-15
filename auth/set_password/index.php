<?php

require_once('../../config/baglanti.php');
require_once "../../includes/functions.php";



try {
    $pdo->beginTransaction();
    if ($_POST) {
        $email = $functions->safeOrNotControl($_POST, 'email');
        $newPassword = $functions->safeOrNotControl($_POST, "newPassword");
        if ($email) {
            // $checkUser = $pdo->prepare("Select * from students Where email = ?");
            // $checkUser->execute([$email]);
            $checkUser = query($pdo, "Select * from students Where email = ?", [$email]);

            if ($checkUser->rowCount() > 0) {
                $newPassword = sha1(md5($newPassword));
                // $updatePassword = $pdo->prepare("Update students Set password = ? Where email = ?");
                // $updatePassword->execute([$newPassword, $email]);
                $updatePassword = query($pdo, "Update students Set password = ? Where email = ?", [$newPassword, $email]);
                if ($updatePassword->rowCount() > 0) {
                    $pdo->commit();
                    $functions->response($_POST, 200, "Şifre Değiştirme Başarılı", null, true);
                }
            } else {
                $functions->response(null, 404, null, "Kullanıcı Bulunamadı!", false);
            }
        } else {
            $functions->response(null, 402, null, "Zorunlu alanı doldurun!", false);
        }
    } else {
        $functions->response(null, 406, null, "Geçersiz istek methodu", false);
    }
} catch (Exception $error) {
    $pdo->rollBack();
    $functions->response(null, 500, null, "Sunucu Hatası", false);
    echo ("Exception: " . $e->getMessage());
}
