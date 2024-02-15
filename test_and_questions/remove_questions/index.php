<?php

require_once '../../config/baglanti.php';
require_once '../../includes/functions.php';



try {
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        $functions->response(null, 406, null, "Geçersiz istek methodu", false);
    } else {
        $pdo->beginTransaction();
        if ($functions->check("QSTN", 5)) {
            $questionID = $functions->safeOrNotControl($_GET, "questionID");
            if (is_string($questionID)) {
                $questionIDs = explode(',', $questionID);
            }
            foreach ($questionIDs as $question) {
                $removeOptions = query($pdo, "DELETE FROM options WHERE question_id = ?", [$question]);
                if ($removeOptions->rowCount() > 0) {
                    $removeQuestion = query($pdo, "DELETE FROM questions where question_id = ?", [$question]);
                    if ($removeQuestion->rowCount() > 0) {
                        $functions->response($_GET, 200, "Soru Silme İşlemi Başarılı", null, true);
                    } else {
                        $functions->response(null, 405, null, "Soru Silme İşlemi Başarısız (SORULAR)", false);
                    }
                } else {
                    $functions->response(null, 405, null, "Soru Silme İşlemi Başarısız (CEVAPLAR)", false);
                }
            }
        }
        $pdo->commit();
    }
} catch (Exception $error) {
    $pdo->rollBack();
    die("Exception: " . $error->getMessage());
    $functions->response(null, 500, null, "Sunucu Hatası", false);
}
