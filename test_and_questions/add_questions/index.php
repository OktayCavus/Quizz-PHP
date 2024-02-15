<?php
require_once '../../config/baglanti.php';
require_once '../../includes/functions.php';
$functions = new Funcitons();

try {
    $pdo->beginTransaction();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $functions->response(null, 406, null, "Geçersiz istek methodu", false);
        exit;
    }

    $requestHeader = $functions->headerRequest();
    if ($requestHeader === null || !isset($requestHeader[0]["Authorization"])) {
        exit;
    }

    // $token = $requestHeader[0]["Authorization"];

    // // AccessToken'ın doğruluğunu kontrol et
    // $username = $functions->verifyToken($token);
    // if (!$username) {
    //     $functions->response(null, 401, null, "Yetkilendirme başarısız", false);
    //     exit;
    // }



    $testID = $functions->safeOrNotControl($_POST, "testID");
    $questionText = $functions->safeOrNotControl($_POST, "questionText");
    $optionText = $_POST["optionText"];
    $isCorrect = $_POST["isCorrect"];


    if (!$testID || !$questionText || !$optionText || !$isCorrect) {
        $functions->response(null, 402, null, "Zorunlu Alanları Doldurun!", false);
        exit;
    }


    $questions = $db->query($pdo, "INSERT INTO questions (test_id, question_text) VALUES (?, ?)", [$testID, $questionText]);
    if ($questions->rowCount() <= 0) {
        $functions->response(null, 402, null, "Soru eklenemedi", false);
        exit;
    }


    $lastInsertID = $pdo->lastInsertId();


    $optionTextList = explode(",", $optionText);
    $isCorrectList = explode(",", $isCorrect);
    foreach ($optionTextList as $index => $option) {
        $optionText = trim($option, "[]\"");
        $isCorrect = trim($isCorrectList[$index], "[]\"");
        $options = $db->query($pdo, "INSERT INTO options (question_id, option_text, is_correct) VALUES (?,?,?)", [$lastInsertID, $optionText, $isCorrect]);
        if ($options->rowCount() <= 0) {
            $functions->response(null, 402, null, "Cevaplar Eklenemedi", false);
            exit;
        }
    }

    $functions->response($_POST, 200, "Soru ve Cevaplar Başarıyla Eklendi", null, true);

    $pdo->commit();
} catch (Exception $error) {
    $functions->response(null, 500, null, "Sunucu Hatası", false);
    $pdo->rollBack();
    die($error->getMessage());
}
