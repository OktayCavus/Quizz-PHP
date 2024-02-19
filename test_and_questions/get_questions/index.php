<?php
require_once '../../config/baglanti.php';
require_once "../../includes/constant.php";
require_once '../../includes/functions.php';
class QuestionGetter
{
    private $db;
    private $functions;
    private $pdo;

    public function __construct()
    {
        $this->functions = new Functions();
        $this->db = new Database();
        $this->pdo = $this->db->getPdo();
    }

    public function getQuestion()
    {
        try {
            if ($_SERVER["REQUEST_METHOD"] !== "GET") {
                $this->functions->response(null, 406, null, ERR_INVALID_REQUEST_METHOD, false);
            } else {
                $this->pdo->beginTransaction();
                $requestHeader = $this->functions->headerRequest();
                if ($requestHeader == null || !isset($requestHeader[0]["Authorization"])) {
                    exit;
                }
                $token = str_replace('Bearer ', '', $requestHeader[0]["Authorization"]);
                $username = $this->functions->verifyToken($token);
                if ($username == $_SESSION["user"]["username"]) {
                    if ($this->functions->check("QSTN", 6)) {
                        $testID = $this->functions->safeOrNotControl($_GET, 'testID');
                        if (!$testID) {
                            $this->functions->response(null, 402, null, ERR_FILL_REQUIRED_FIELDS, false);
                            exit;
                        }
                        $questions = $this->db->query("
                        SELECT * FROM questions AS q INNER JOIN options AS o ON q.question_id = o.question_id WHERE q.test_id = ?", [$testID]);

                        if ($questions->rowCount()) {
                            $result = $questions->fetchAll(PDO::FETCH_ASSOC);
                            $allQuestions = array();

                            foreach ($result as $question) {
                                $question_id = $question['question_id'];
                                if (!isset($allQuestions[$question_id])) {
                                    $allQuestions[$question_id] = array(
                                        'question_id' => $question['question_id'],
                                        'test_id' => $question['test_id'],
                                        'question_text' => $question['question_text'],
                                        'created_at' => $question['created_at'],
                                        'options' => array()
                                    );
                                }
                                // ! burdaki [] bu her gelenin üzerine yazılmaması için var eğer olmasaydı en son gelen cevap yazılacaktı şuan 
                                /*{
                "question_id": 191,
                "test_id": 1,
                "question_text": "Soru Metni",
                "created_at": "2024-02-19 11:48:14",
                "options": [
                    {
                        "option_text": "seçenekA",
                        "is_correct": 0
                    },
                    {
                        "option_text": "seçenekB",
                        "is_correct": 0
                    },
                    {
                        "option_text": "seçenekC",
                        "is_correct": 1
                    },
                    {
                        "option_text": "seçenekD",
                        "is_correct": 0
                    }
                ]
            }, */ // ! böyle ama o olmasaydı
                                /*"questions": [
            {
                "question_id": 191,
                "test_id": 1,
                "question_text": "Soru Metni",
                "created_at": "2024-02-19 11:48:14",
                "options": {
                    "option_id": 628,
                    "option_text": "seçenekD",
                    "is_correct": 0
                }
            },*/ // ! böyle olurdu
                                $allQuestions[$question_id]['options'][] = array(
                                    'option_id' => $question['option_id'],
                                    'option_text' => $question['option_text'],
                                    'is_correct' => $question['is_correct']
                                );
                            }

                            $responseContent = array(
                                'questions' => array_values($allQuestions)
                            );
                            $this->functions->response($responseContent, 200, MESSAGE_SUCCESS_QUESTION_LISTING, null, true);
                        } else {
                            $this->functions->response(null, 402, null, ERR_QUESTION_LISTING_FAILED, false);
                            exit;
                        }
                    }
                } else {
                    $this->functions->response(null, 401, null, ERR_UNAUTHORIZED_ACCESS, false);
                }
            }
        } catch (Exception $e) {
            $this->functions->response(null, 500, null, ERR_SERVER_ERROR, false);
            $this->pdo->rollBack();
            die($e->getMessage());
        }
    }
}

$questionGetter = new QuestionGetter();
$questionGetter->getQuestion();
