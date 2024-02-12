<?php

require_once '../../config/baglanti.php';
require_once '../../includes/functions.php';



try {
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        response(null, 406, null, "Geçersiz istek methodu", false);
    } else {
        $pdo->beginTransaction();
        if (check("QSTN", 5)) {
            $questionID = safeOrNotControl($_GET, "questionID");
            if (is_string($questionID)) {
                $questionIDs = explode(',', $questionID);
            }
            foreach ($questionIDs as $question) {
                $removeOptions = query($pdo, "DELETE FROM options WHERE question_id = ?", [$question]);
                if ($removeOptions->rowCount() > 0) {
                    $removeQuestion = query($pdo, "DELETE FROM questions where question_id = ?", [$question]);
                    if ($removeQuestion->rowCount() > 0) {
                        response($_GET, 200, "Soru Silme İşlemi Başarılı", null, true);
                    } else {
                        response(null, 405, null, "Soru Silme İşlemi Başarısız (SORULAR)", false);
                    }
                } else {
                    response(null, 405, null, "Soru Silme İşlemi Başarısız (CEVAPLAR)", false);
                }
            }
        }
        $pdo->commit();
    }
} catch (Exception $error) {
    $pdo->rollBack();
    die("Exception: " . $error->getMessage());
    response(null, 500, null, "Sunucu Hatası", false);
}
