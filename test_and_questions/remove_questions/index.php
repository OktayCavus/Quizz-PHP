<?php

require_once '../../config/baglanti.php';
require_once '../../includes/functions.php';

class QuestionRemover
{
    private $db;
    private $functions;
    private $pdo;

    public function __construct()
    {
        $this->db = new Database();
        $this->functions = new Functions();
        $this->pdo = $this->db->getPdo();
    }

    public function removeQuestion()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
                $this->functions->response(null, 406, null, "Geçersiz istek methodu", false);
            } else {
                $this->pdo->beginTransaction();
                $requestHeader = $this->functions->headerRequest();
                if ($requestHeader === null || !isset($requestHeader[0]["Authorization"])) {
                    exit;
                }
                $token = str_replace('Bearer ', '', $requestHeader[0]["Authorization"]);
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
                                    $this->functions->response($_GET, 200, "Soru Silme İşlemi Başarılı", null, true);
                                } else {
                                    $this->functions->response(null, 405, null, "Soru Silme İşlemi Başarısız (SORULAR)", false);
                                }
                            } else {
                                $this->functions->response(null, 405, null, "Soru Silme İşlemi Başarısız (CEVAPLAR)", false);
                            }
                        }
                    }
                } else {
                    $this->functions->response(null, 401, null, "Yetkisiz Erişim (Tokenler uyuşmuyor)", false);
                }
                $this->pdo->commit();
            }
        } catch (Exception $error) {
            $this->pdo->rollBack();
            die("Exception: " . $error->getMessage());
            $this->functions->response(null, 500, null, "Sunucu Hatası", false);
        }
    }
}

$questionRemover = new QuestionRemover();
$questionRemover->removeQuestion();
