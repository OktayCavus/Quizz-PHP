<?php

require_once '../../config/baglanti.php';
require_once '../../includes/functions.php';

try {
    $pdo->beginTransaction();

    if (check("QSTN", 5)) {
        $questionID = safeOrNotControl($_GET, "questionID");
        $removeOptions = query($pdo, "DELETE FROM options WHERE question_id = ?", [$questionID]);
        if ($removeOptions->rowCount() > 0) {
            $removeQuestion = query($pdo, "DELETE FROM questions where question_id = ?", [$questionID]);
            if ($removeQuestion->rowCount() > 0) {
                response($_GET, 200, "Soru Silme İşlemi Başarılı", null, true);
            } else {
                response(null, 405, null, "Soru Silme İşlemi Başarısız (SORULAR)", false);
            }
        } else {
            response(null, 405, null, "Soru Silme İşlemi Başarısız (CEVAPLAR)", false);
        }
    }
    $pdo->commit();
} catch (Exception $error) {
    $pdo->rollBack();
    die("Exception: " . $error->getMessage());
    response(null, 500, null, "Sunucu Hatası", false);
}
