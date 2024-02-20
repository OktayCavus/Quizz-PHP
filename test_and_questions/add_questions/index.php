<?php

require_once '../../config/baglanti.php';
require_once '../../includes/functions.php';
require_once '../../languages/language.php';
class QuestionManager
{
    private $db;
    private $pdo;
    private $functions;
    private $lang;
    private $requestHeader;
    public function __construct()
    {
        $this->functions = new Functions();
        $this->db = new Database();
        $this->pdo = $this->db->getPdo();
        $this->requestHeader = $this->functions->headerRequest();
        $selectedLang = $this->requestHeader[0]['Accept-Language'];
        $this->lang = new Language($selectedLang);
    }

    public function addQuestion()
    {
        try {
            $this->pdo->beginTransaction();

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->functions->response(null, 406, null,  $this->lang->getMessage('ERR_INVALID_REQUEST_METHOD'), false);
                exit;
            }

            if ($this->requestHeader === null || !isset($this->requestHeader[0]["Authorization"])) {
                exit;
            }
            $token = str_replace('Bearer ', '', $this->requestHeader[0]["Authorization"]);
            $username = $this->functions->verifyToken($token);

            if ($username == $_SESSION["user"]["username"]) {
                if ($this->functions->check("QSTN", 5)) {
                    $testID = $this->functions->safeOrNotControl($_POST, "testID");
                    $questionText = $this->functions->safeOrNotControl($_POST, "questionText");
                    $optionText = $_POST["optionText"];
                    $isCorrect = $_POST["isCorrect"];

                    if (!$testID || !$questionText || !$optionText || !$isCorrect) {
                        $this->functions->response(null, 402, null, $this->lang->getMessage('ERR_FILL_REQUIRED_FIELDS'), false);
                        exit;
                    }

                    $questions = $this->db->query("INSERT INTO questions (test_id, question_text) VALUES (?, ?)", [$testID, $questionText]);
                    if ($questions->rowCount() <= 0) {
                        $this->functions->response(null, 402, null, $this->lang->getMessage('ERR_QUESTION_ADD_FAILED'), false);
                        exit;
                    }

                    $lastInsertID = $this->pdo->lastInsertId();
                    $optionTextList = explode(",", $optionText);
                    $isCorrectList = explode(",", $isCorrect);

                    foreach ($optionTextList as $index => $option) {
                        $optionText = trim($option, "[]\"");
                        $isCorrect = trim($isCorrectList[$index], "[]\"");
                        $options = $this->db->query("INSERT INTO options (question_id, option_text, is_correct) VALUES (?,?,?)", [$lastInsertID, $optionText, $isCorrect]);
                        if ($options->rowCount() <= 0) {
                            $this->functions->response(null, 402, null, $this->lang->getMessage('ERR_QUESTION_ADD_FAILED'), false);
                            exit;
                        }
                    }

                    $this->functions->response($_POST, 200, $this->lang->getMessage('MESSAGE_SUCCESS_QUESTION_ADDED'), null, true);

                    $this->pdo->commit();
                }
            } else {
                $this->functions->response(null, 401, null, $this->lang->getMessage('ERR_UNAUTHORIZED_ACCESS'), false);
            }
        } catch (Exception $error) {
            $this->functions->response(null, 500, null,  $this->lang->getMessage('ERR_SERVER_ERROR'), false);
            $this->pdo->rollBack();
            die($error->getMessage());
        }
    }
}

$questionManager = new QuestionManager();
$questionManager->addQuestion();
