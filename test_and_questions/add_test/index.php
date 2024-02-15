<?php
require_once '../../config/baglanti.php';
require_once '../../includes/functions.php';

$db = new Database();
$pdo = $db->getPdo();
try {
    $pdo->beginTransaction();
    if ($_POST) {
        if ($functions->check("QSTN", 4)) {
            $testName = $functions->safeOrNotControl($_POST, "testName");
            $questionCount = $functions->safeOrNotControl($_POST, "questionCount");
            if ($testName && $questionCount) {

                $test = $db->query($pdo, "Insert into tests ( test_name , question_count) values (? , ?)", [$testName, $questionCount]);
                if ($test->rowCount() > 0) {
                    $functions->response($_POST, 200, "Test Başarıyla Eklendi", null, true);
                } else {
                    $functions->response(null, 403, null, "Test Eklenemedi", false);
                }
            } else {

                $functions->response(null, 402, null, "Zorunlu alanları doldurun!", false);
            }
        }
        $pdo->commit();
    } else {
        $functions->response(null, 406, null, "Geçersiz istek methodu", false);
    }
} catch (Exception $error) {
    $pdo->rollBack();
    die("Exception: " . $error->getMessage());
    $functions->response(null, 500, null, "Sunucu Hatası", false);
}
