<?php
// Gerekli dosyaları dahil ediyoruz
require_once "../../includes/functions.php";
require_once "../../config/baglanti.php";

// Yanıtın JSON formatında olacağını belirtiyoruz
header("Content-Type: application/json; charset=utf-8");

try {
    if ($_POST) {
        $username = safeOrNotControl($_POST, "username");
        $password = safeOrNotControl($_POST, "password");

        if ($username && $password) {
            $password = sha1(md5($password));

            // Kullanıcıyı ve rolünü sorgula
            // $checkAuth = $pdo->prepare("SELECT * FROM students JOIN roles ON students.role_id = roles.role_id WHERE username = ? && password = ?");
            // $checkAuth->execute([$username, $password]);
            $checkAuth = query(
                $pdo,
                "SELECT * FROM students JOIN roles ON students.role_id = roles.role_id WHERE username = ? && password = ?",
                [$username, $password]
            );

            if ($checkAuth->rowCount() > 0) {


                // Kullanıcı bilgilerini al
                $user = $checkAuth->fetch(PDO::FETCH_ASSOC);

                // Kullanıcının izinlerini al
                // $userPermission = $pdo->prepare("SELECT * FROM `roles_permissions` r LEFT JOIN `permissions` p USING (`perm_id`) WHERE r.`role_id`=?");
                // $userPermission->execute([$user["role_id"]]);
                $userPermission = query($pdo, "SELECT * FROM `roles_permissions` r LEFT JOIN `permissions` p USING (`perm_id`) WHERE r.`role_id`=?", [$user["role_id"]]);
                while ($r = $userPermission->fetch(PDO::FETCH_ASSOC)) {
                    if (!isset($user["permissions"][$r["perm_mod"]])) {
                        $user["permissions"][$r["perm_mod"]] = [];
                    }
                    $user["permissions"][$r["perm_mod"]][] = $r["perm_id"];
                }

                // Kullanıcı bilgilerini oturumda sakla
                unset($user["password"]);
                $_SESSION["user"] = $user;

                response($user, 200, "Kullanıcı Girişi Başarılı", null, true);
            } else {
                response(null, 400, null, "Kullanıcı Adı veya Şifre Hatalı.!", false);
            }
        } else {
            response(null, 400, null, "Kullanıcı Adı ve Şifre boş bırakılamaz.!", false);
        }
    }
} catch (Exception $e) {
    echo ("Exception: " . $e->getMessage());
    response(null, 500, null, "Sunucu Hatası", false);
}
