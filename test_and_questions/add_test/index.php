<?php
require_once '../../config/baglanti.php';
require_once '../../includes/functions.php';
require_once "../../includes/constant.php";

class TestController
{
    private $db;
    private $functions;
    private $pdo;

    public function __construct()
    {
        $this->db = new Database();
        $this->pdo = $this->db->getPdo();
        $this->functions = new Functions();
    }

    public function addTest()
    {
        $requestHeader = $this->functions->headerRequest();
        if ($requestHeader === null || !isset($requestHeader[0]["Authorization"])) {
            exit;
        }
        $token = str_replace('Bearer ', '', $requestHeader[0]["Authorization"]);
        $username = $this->functions->verifyToken($token);

        try {
            $this->pdo->beginTransaction();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if ($username == $_SESSION["user"]["username"]) {

                    if ($this->functions->check("QSTN", 4)) {
                        $testName = $this->functions->safeOrNotControl($_POST, "testName");
                        $questionCount = $this->functions->safeOrNotControl($_POST, "questionCount");
                        if ($testName && $questionCount) {
                            $test = $this->db->query("INSERT INTO tests (test_name, question_count) VALUES (?, ?)", [$testName, $questionCount]);
                            if ($test->rowCount() > 0) {
                                $this->functions->response($_POST, 200, MESSAGE_SUCCESS_TEST_ADDED, null, true);
                            } else {
                                $this->functions->response(null, 403, null, ERR_FILL_REQUIRED_FIELDS, false);
                            }
                        } else {
                            $this->functions->response(null, 402, null, ERR_FILL_REQUIRED_FIELDS, false);
                        }
                    }
                } else {
                    $this->functions->response(null, 401, null, ERR_UNAUTHORIZED_ACCESS, false);
                }
                $this->pdo->commit();
            } else {
                $this->functions->response(null, 406, null, ERR_INVALID_REQUEST_METHOD, false);
            }
        } catch (Exception $error) {
            $this->pdo->rollBack();
            die("Exception: " . $error->getMessage());
            $this->functions->response(null, 500, null, ERR_SERVER_ERROR, false);
        }
    }
}

$testController = new TestController();
$testController->addTest();
