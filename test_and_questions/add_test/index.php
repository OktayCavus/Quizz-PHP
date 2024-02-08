<?php
require_once '../../config/baglanti.php';
require_once '../../includes/functions.php';


try {
    $pdo->beginTransaction();
    if ($_POST) {
        if (check("QSTN", 4)) {
            $testName = safeOrNotControl($_POST, "testName");
            $questionCount = safeOrNotControl($_POST, "questionCount");
            if ($testName && $questionCount) {
                // $test = $pdo->prepare("Insert into tests ( test_name , question_count) values (:testName , :questionCount)");
                // $test->bindParam(':testName', $testName);
                // $test->bindParam(':questionCount', $questionCount);
                // $test->execute();
                $test = query($pdo, "Insert into tests ( test_name , question_count) values (? , ?)", [$testName, $questionCount]);
                if ($test->rowCount() > 0) {
                    response($_POST, 200, "Test Başarıyla Eklendi", null, true);
                } else {
                    response(null, 403, null, "Test Eklenemedi", false);
                }
            } else {

                response(null, 402, null, "Zorunlu alanları doldurun!", false);
            }
        }
        $pdo->commit();
    }
} catch (Exception $error) {
    $pdo->rollBack();
    die("Exception: " . $error->getMessage());
    response(null, 500, null, "Sunucu Hatası", false);
}
