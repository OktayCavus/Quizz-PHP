<?php

require_once('../../config/baglanti.php');
require_once "../../includes/functions.php";

class UserRegistration
{
    private $functions;
    private $db;

    public function __construct($functions, $db)
    {
        $this->functions = $functions;
        $this->db = $db;
    }

    public function registerUser()
    {
        $this->db->beginTransaction();

        if ($_POST) {
            $islem = array();
            $username = $this->functions->safeOrNotControl($_POST, 'username');
            $password = $this->functions->safeOrNotControl($_POST, 'password');
            $role_id = $this->functions->safeOrNotControl($_POST, 'role_id');
            $firstname = $this->functions->safeOrNotControl($_POST, 'firstname');
            $lastname = $this->functions->safeOrNotControl($_POST, 'lastname');
            $email = $this->functions->safeOrNotControl($_POST, 'email');
            $phonenumber = $this->functions->safeOrNotControl($_POST, 'phonenumber');

            if ($username && $password && $role_id && $firstname && $lastname && $email) {
                if (strlen($password) >= 6) {
                    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $this->functions->response(NULL, 403, NULL, "Geçersiz e-posta error", false);
                    } else {
                        $checkUsername = $this->db->query("Select * From students Where username = ? ", [$username]);
                        if ($checkUsername->rowCount() > 0) {
                            $this->functions->response(null, 400, null, "Kullanıcı adı kullanılıyor.", false);
                        } else {
                            $checkEmail = $this->db->query("Select * From students Where email = ?", [$email]);
                            if ($checkEmail->rowCount() > 0) {
                                $this->functions->response(null, 400, null, "Bu E-posta adresi kullanılıyor.", false);
                            } else {
                                try {
                                    $password = sha1(md5($password));
                                    $sorgu = $this->db->query(
                                        "INSERT INTO students (username, password, role_id , first_name, last_name, email, phone_number) VALUES (?, ?, ?, ?, ?, ?, ?)",
                                        [$username, $password, $role_id, $firstname, $lastname, $email, $phonenumber]
                                    );
                                    if ($sorgu->rowCount() > 0) {
                                        unset($_POST['password']);
                                        $this->functions->response($_POST, 200, "Kayıt eklendi", NULL, true);
                                    } else {
                                        $this->functions->response(NULL, 201, NULL, "Kayıt Eklenemedi", false);
                                    }
                                } catch (PDOException $e) {
                                    $this->functions->response(null, 500, null, "Sunucu Hatası", false);
                                    $this->db->rollBack();
                                    echo 'Veri eklenirken hata oluştu: ' . $e->getMessage();
                                }
                            }
                        }
                    }
                } else {
                    $this->functions->response(NULL, 402, NULL, "Şifre en az 6 karakter olmalıdır.", false);
                }
            } else {
                $this->functions->response(NULL, 400, NULL, "Zorunlu alanları doldurun: username, password, firstname, lastname", false);
            }
            $this->db->commit();
        } else {
            $this->functions->response(null, 406, null, "Geçersiz istek methodu", false);
        }
    }
}

$functions = new Funcitons();
$db = new Database();
$registrationHandler = new UserRegistration($functions, $db);
$registrationHandler->registerUser();
