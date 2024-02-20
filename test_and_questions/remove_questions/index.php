<?php

require_once '../../config/baglanti.php';

require_once '../../includes/functions.php';
require_once '../../languages/language.php';

class QuestionRemover
{
    private $db;
    private $functions;
    private $pdo;
    private $lang;
    private $requestHeader;

    public function __construct()
    {
        $this->db = new Database();
        $this->functions = new Functions();
        $this->pdo = $this->db->getPdo();
        $this->lang = new Language('tr');
        $this->requestHeader = $this->functions->headerRequest();
        $selectedLang = $this->requestHeader[0]['Accept-Language'];
        $this->lang = new Language($selectedLang);
    }

    public function removeQuestion()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
                $this->functions->response(null, 406, null, 'ERR_INVALID_REQUEST_METHOD', false);
            } else {
                $this->pdo->beginTransaction();

                if ($this->requestHeader === null || !isset($this->requestHeader[0]["Authorization"])) {
                    exit;
                }
                $token = str_replace('Bearer ', '', $this->requestHeader[0]["Authorization"]);
                $username = $this->functions->verifyToken($token);

                if ($username == $_SESSION["user"]["username"]) {

                    if ($this->functions->check("QSTN", 5)) {
                        $questionID = $this->functions->safeOrNotControl($_GET, "questionID");
                        if (is_string($questionID)) {
                            $questionIDs = explode(',', $questionID);
                        }
                        foreach ($questionIDs as $question) {
                            $removeOptions = $this->db->query("DELETE FROM options WHERE question_id = ?", [$question]);
                            if ($removeOptions->rowCount() > 0) {
                                $removeQuestion = $this->db->query("DELETE FROM questions where question_id = ?", [$question]);
                                if ($removeQuestion->rowCount() > 0) {
                                    $this->functions->response($_GET, 200, 'MESSAGE_SUCCESS_QUESTION_DELETE', null, true);
                                } else {
                                    $this->functions->response(null, 405, null, 'ERR_QUESTION_DELETE_FAILED_Q', false);
                                }
                            } else {
                                $this->functions->response(null, 405, null, 'ERR_QUESTION_DELETE_FAILED_A', false);
                            }
                        }
                    }
                } else {
                    $this->functions->response(null, 401, null, 'ERR_UNAUTHORIZED_ACCESS', false);
                }
                $this->pdo->commit();
            }
        } catch (Exception $error) {
            $this->pdo->rollBack();
            die("Exception: " . $error->getMessage());
            $this->functions->response(null, 500, null, 'ERR_SERVER_ERROR', false);
        }
    }
}

$questionRemover = new QuestionRemover();
$questionRemover->removeQuestion();
