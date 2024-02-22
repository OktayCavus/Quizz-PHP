<?php

use Firebase\JWT\JWT;
use Firebase\JWT\KEY;

require_once "../../includes/functions.php";
require_once "../../includes/base_controller.php";

require_once '../../languages/language.php';
require_once "../../config/baglanti.php";
require_once __DIR__ . "/../../vendor/autoload.php";

class AuthenticationHandler extends BaseController
{
    // private $functions;
    // private $db;
    // private $lang;
    // private $languageHeader;
    // private $selectedLang;

    public function __construct($functions, $db)
    {
        // $this->functions = $functions;
        // $this->db = $db;
        // $this->languageHeader = apache_request_headers();
        // $this->selectedLang = $this->languageHeader['Accept-Language'];
        // $this->lang = new Language($this->selectedLang);
        parent::__construct(false);
    }

    public function handleAuthentication()
    {
        $this->db->beginTransaction();

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->functions->response(null, 406, null, 'ERR_INVALID_REQUEST_METHOD', false);
                exit;
            }

            $username = $this->functions->safeOrNotControl($_POST, "username");
            $password = $this->functions->safeOrNotControl($_POST, "password");

            if (!$username || !$password) {
                $this->functions->response(null, 400, null, 'ERR_EMPTY_USERNAME_OR_PASSWORD', false);
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
                print_r($_SESSION["user"]["username"]);
                $secret_key = $_ENV["SECRET_KEY"];
                $expiry_time = time() + (60 * 60) * 24;
                $payload = array(
                    "username" => $user["username"],
                    "exp" => $expiry_time
                );

                $access_token = JWT::encode($payload, $secret_key, 'HS256');
                $_SESSION["accessToken"] = $access_token;

                $headers = [
                    'Authorization: Bearer ' .  $_SESSION["accessToken"],
                    'Content-Type: application/json',
                    'Accept-Language: ' . $this->selectedLang
                ];
                foreach ($headers as $header) {
                    header($header);
                }


                $user["accessToken"] = $_SESSION["accessToken"];
                $this->functions->response($user, 200, 'MESSAGE_SUCCESS_LOGIN', null, true);
            } else {
                $this->functions->response(null, 400, null, 'ERR_INCORRECT_USERNAME_OR_PASSWORD', false);
            }
            $this->db->commit();
        } catch (Exception $e) {
            echo ("Exception: " . $e->getMessage());
            $this->db->rollBack();
            $this->functions->response(null, 500, null, 'ERR_SERVER_ERROR', false);
        }
    }
}

$functions = new Functions();
$db = new Database();
$authenticationHandler = new AuthenticationHandler($functions, $db);
$authenticationHandler->handleAuthentication();
