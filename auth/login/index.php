<?php
require_once "../../includes/functions.php";
require_once "../../config/baglanti.php";


header("Content-Type: application/json; charset=utf-8");

$pdo->beginTransaction();

try {
    if ($_POST) {
        $username = safeOrNotControl($_POST, "username");
        $password = safeOrNotControl($_POST, "password");

        if ($username && $password) {
            $password = sha1(md5($password));
            $checkAuth = $pdo->prepare("Select * from students Where username = ? && password = ?");
            $checkAuth->execute([$username, $password]);

            if ($checkAuth->rowCount() > 0) {
                session_start();
                session_regenerate_id(true);

                $_SESSION["username"] = $username;
                // unset($_POST["password"]);
                response($username, 200, "Kullanıcı Girişi Başarılı", null, true);
            } else {
                response(null, 400, null, "Kullanıcı Adı veya Şifre Hatalı.!", false);
            }
        } else {
            response(null, 400, null, "Kullanıcı Adı ve Şifre boş bırakılamaz.!", false);
        }
        $pdo->commit();
    }
} catch (Exception $e) {
    $pdo->rollBack();
    echo ("Exception: " . $e->getMessage());
    response(null, 500, null, "Sunucu Hatası", false);
}
