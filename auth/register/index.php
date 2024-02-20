<?php

require_once('../../config/baglanti.php');
require_once "../../includes/functions.php";

require_once '../../languages/language.php';


class UserRegistration
{
    private $functions;
    private $db;
    private $defaultRoleID;
    private $lang;
    private $languageHeader;
    private $selectedLang;
    public function __construct($functions, $db)
    {
        $this->functions = $functions;
        $this->db = $db;
        $this->defaultRoleID = 2;

        $this->languageHeader = apache_request_headers();
        $this->selectedLang = $this->languageHeader['Accept-Language'];
        $this->lang = new Language($this->selectedLang);
    }

    public function registerUser()
    {
        $this->db->beginTransaction();

        if ($_POST) {
            $islem = array();
            $username = $this->functions->safeOrNotControl($_POST, 'username');
            $password = $this->functions->safeOrNotControl($_POST, 'password');
            // $role_id = $this->functions->safeOrNotControl($_POST, 'role_id');
            $role_id = $this->defaultRoleID;
            $firstname = $this->functions->safeOrNotControl($_POST, 'firstname');
            $lastname = $this->functions->safeOrNotControl($_POST, 'lastname');
            $email = $this->functions->safeOrNotControl($_POST, 'email');
            $phonenumber = $this->functions->safeOrNotControl($_POST, 'phonenumber');

            if ($username && $password && $role_id && $firstname && $lastname && $email) {
                if (strlen($password) >= 6) {
                    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $this->functions->response(NULL, 403, NULL, 'ERR_INVALID_EMAIL', false);
                    } else {
                        $checkUsername = $this->db->query("Select * From students Where username = ? ", [$username]);
                        if ($checkUsername->rowCount() > 0) {
                            $this->functions->response(null, 400, null, 'ERR_USERNAME_ALREADY_IN_USE', false);
                        } else {
                            $checkEmail = $this->db->query("Select * From students Where email = ?", [$email]);
                            if ($checkEmail->rowCount() > 0) {
                                $this->functions->response(null, 400, null, 'ERR_EMAIL_ALREADY_IN_USE', false);
                            } else {
                                try {
                                    $password = sha1(md5($password));
                                    $sorgu = $this->db->query(
                                        "INSERT INTO students (username, password, role_id , first_name, last_name, email, phone_number) VALUES (?, ?, ?, ?, ?, ?, ?)",
                                        [$username, $password, $role_id, $firstname, $lastname, $email, $phonenumber]
                                    );
                                    if ($sorgu->rowCount() > 0) {
                                        unset($_POST['password']);
                                        $this->functions->response($_POST, 200, 'MESSAGE_RECORD_ADDED', NULL, true);
                                    } else {
                                        $this->functions->response(NULL, 201, NULL, 'ERR_RECORD_NOT_ADDED', false);
                                    }
                                } catch (PDOException $e) {
                                    $this->functions->response(null, 500, null, 'ERR_SERVER_ERROR', false);
                                    $this->db->rollBack();
                                    die($e->getMessage());
                                }
                            }
                        }
                    }
                } else {
                    $this->functions->response(NULL, 402, NULL, 'ERR_PASSWORD_MINIMUM_LENGTH', false);
                }
            } else {
                $this->functions->response(NULL, 400, NULL,  'ERR_FILL_REQUIRED_FIELDS', false);
            }
            $this->db->commit();
        } else {
            $this->functions->response(null, 406, null, 'ERR_INVALID_REQUEST_METHOD', false);
        }
    }
}

$functions = new Functions();
$db = new Database();
$registrationHandler = new UserRegistration($functions, $db);
$registrationHandler->registerUser();
