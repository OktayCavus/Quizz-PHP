<?php

require_once('../../config/baglanti.php');
require_once "../../includes/functions.php";

require_once '../../languages/language.php';


class PasswordReset
{
    private $db;
    private $pdo;
    private $functions;
    private $lang;
    private $languageHeader;
    private $selectedLang;
    public function __construct()
    {
        $this->db = new Database();
        $this->pdo = $this->db->getPdo();
        $this->functions = new Functions();
        $this->languageHeader = apache_request_headers();
        $this->selectedLang = $this->languageHeader['Accept-Language'];
        $this->lang = new Language($this->selectedLang);
    }

    public function resetPassword()
    {
        try {
            $this->pdo->beginTransaction();

            if ($_POST) {
                $email = $this->functions->safeOrNotControl($_POST, 'email');
                $newPassword = $this->functions->safeOrNotControl($_POST, "newPassword");

                if ($email && $newPassword) {
                    $checkUser = $this->db->query("SELECT * FROM students WHERE email = ?", [$email]);

                    if ($checkUser->rowCount() > 0) {
                        $newPassword = sha1(md5($newPassword));
                        $updatePassword = $this->db->query("UPDATE students SET password = ? WHERE email = ?", [$newPassword, $email]);

                        if ($updatePassword->rowCount() > 0) {
                            $this->pdo->commit();
                            $this->functions->response($_POST, 200, $this->lang->getMessage('MESSAGE_PASSWORD_CHANGE_SUCCESSFUL'), null, true);
                        }
                    } else {
                        $this->functions->response(null, 404, null, $this->lang->getMessage('ERR_USER_NOT_FOUND'), false);
                    }
                } else {
                    $this->functions->response(null, 402, null,  $this->lang->getMessage('ERR_FILL_REQUIRED_FIELDS'), false);
                }
            } else {
                $this->functions->response(null, 406, null, $this->lang->getMessage('ERR_INVALID_REQUEST_METHOD'), false);
            }
        } catch (Exception $error) {
            $this->pdo->rollBack();
            $this->functions->response(null, 500, null, $this->lang->getMessage('ERR_SERVER_ERROR'), false);
            echo ("Exception: " . $error->getMessage());
        }
    }
}


$passwordReset = new PasswordReset();
$passwordReset->resetPassword();
