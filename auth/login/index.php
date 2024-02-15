<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once "../../includes/functions.php";
require_once "../../config/baglanti.php";
require_once __DIR__ . "/../../vendor/autoload.php";

class AuthenticationHandler
{
    private $functions;
    private $db;

    public function __construct($functions, $db)
    {
        $this->functions = $functions;
        $this->db = $db;
    }

    public function handleAuthentication()
    {
        $this->db->beginTransaction();

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->functions->response(null, 406, null, "Geçersiz istek methodu", false);
                exit;
            }

            $username = $this->functions->safeOrNotControl($_POST, "username");
            $password = $this->functions->safeOrNotControl($_POST, "password");

            if (!$username || !$password) {
                $this->functions->response(null, 400, null, "Kullanıcı Adı ve Şifre boş bırakılamaz.!", false);
                exit;
            }

            $password = sha1(md5($password));

            $checkAuth = $this->db->query(
                "SELECT * FROM students JOIN roles ON students.role_id = roles.role_id WHERE username = ? && password = ?",
                [$username, $password]
            );

            if ($checkAuth->rowCount() > 0) {
                $user = $checkAuth->fetch(PDO::FETCH_ASSOC);
                $userPermission = $this->db->query(
                    "SELECT * FROM `roles_permissions` r LEFT JOIN `permissions` p USING (`perm_id`) WHERE r.`role_id`=?",
                    [$user["role_id"]]
                );
                while ($r = $userPermission->fetch(PDO::FETCH_ASSOC)) {
                    if (!isset($user["permissions"][$r["perm_mod"]])) {
                        $user["permissions"][$r["perm_mod"]] = [];
                    }
                    $user["permissions"][$r["perm_mod"]][] = $r["perm_id"];
                }

                unset($user["password"]);

                $_SESSION["user"] = $user;

                $secret_key = $_ENV["SECRET_KEY"];
                $expiry_time = time() + (60 * 60);
                $payload = array(
                    "username" => $user["username"],
                    "exp" => $expiry_time
                );

                $access_token = JWT::encode($payload, $secret_key, 'HS256');
                $headers = [
                    'Authorization: Bearer ' .  $access_token,
                    'Content-Type: application/json'
                ];

                foreach ($headers as $header) {
                    header($header);
                }

                $this->functions->response($user, 200, "Kullanıcı Girişi Başarılı", null, true, $headers);
            } else {
                $this->functions->response(null, 400, null, "Kullanıcı Adı veya Şifre Hatalı.!", false);
            }
            $this->db->commit();
        } catch (Exception $e) {
            echo ("Exception: " . $e->getMessage());
            $this->db->rollBack();
            $this->functions->response(null, 500, null, "Sunucu Hatası", false);
        }
    }
}

$functions = new Functions();
$db = new Database();
$authenticationHandler = new AuthenticationHandler($functions, $db);
$authenticationHandler->handleAuthentication();
