<?php

require_once '../../config/baglanti.php';
require_once '../../includes/functions.php';
require_once '../../languages/language.php';
require_once '../../includes/base_controller.php';

class QuestionManager extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addQuestion()
    {
        try {
            $this->pdo->beginTransaction();

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->functions->response(null, 406, null, 'ERR_INVALID_REQUEST_METHOD', false);
                exit;
            }

            if ($this->requestHeader === null || !isset($this->requestHeader[0]["Authorization"])) {
                exit;
            }

            $token = str_replace('Bearer ', '', $this->requestHeader[0]["Authorization"]);
            $username = $this->functions->verifyToken($token);

            if (isset($_SESSION["user"])) {
                if ($username == $_SESSION["user"]["username"]) {
                    if ($this->functions->check("QSTN", 5)) {
                        $testID = $this->functions->safeOrNotControl($_POST, "testID");
                        $questionText = $this->functions->safeOrNotControl($_POST, "questionText");
                        $optionText = $_POST["optionText"];
                        $isCorrect = $_POST["isCorrect"];

                        if (!$testID || !$questionText || !$optionText || !$isCorrect) {
                            $this->functions->response(null, 402, null, 'ERR_FILL_REQUIRED_FIELDS', false);
                            exit;
                        }

                        $questions = $this->db->query("INSERT INTO questions (test_id, question_text) VALUES (?, ?)", [$testID, $questionText]);
                        if ($questions->rowCount() <= 0) {
                            $this->functions->response(null, 402, null, 'ERR_QUESTION_ADD_FAILED', false);
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
                                $this->functions->response(null, 402, null, 'ERR_QUESTION_ADD_FAILED', false);
                                exit;
                            }
                        }

                        $this->functions->response($_POST, 200, 'MESSAGE_SUCCESS_QUESTION_ADDED', null, true);

                        $this->pdo->commit();
                    }
                } else {
                    $this->functions->response(null, 401, null, 'ERR_UNAUTHORIZED_ACCESS', false);
                }
            } else {
                $this->functions->response(null, 408, null, 'ERR_SESSION_NOT_STARTED', false);
            }
        } catch (Exception $error) {
            $this->functions->response(null, 500, null, 'ERR_SERVER_ERROR', false);
            $this->pdo->rollBack();
            die($error->getMessage());
        }
    }
}

$questionManager = new QuestionManager();
$questionManager->addQuestion();
