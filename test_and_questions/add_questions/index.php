<?php
require_once '../../config/baglanti.php';
require_once '../../includes/functions.php';

try {

    $pdo->beginTransaction();

    if ($_POST) {
        if (check("QSTN", 4)) {
            $testID =  safeOrNotControl($_POST, "testID");
            $questionText = safeOrNotControl($_POST, "questionText");
            // $questions = $pdo->prepare("INSERT INTO questions (test_id, question_text) VALUES (?, ?)");
            // $questions->execute([$testID, $questionText]);
            $questions = query($pdo, "INSERT INTO questions (test_id, question_text) VALUES (?, ?)", [$testID, $questionText]);
            if ($testID && $questionText) {
                if ($questions->rowCount() > 0) {
                    $lastInsertID = $pdo->lastInsertId();
                    $optionText = $_POST["optionText"];
                    $optionTextList = explode(",", $optionText);
                    $isCorrect = $_POST["isCorrect"];
                    $isCorrectList = explode(",", $isCorrect);
                    if ($optionText !== ""  && $isCorrect !== "") {
                        for ($i = 0; $i < count($optionTextList); $i++) {
                            $optionText = trim($optionTextList[$i], "[]\"");
                            $isCorrect = trim($isCorrectList[$i], "[]\"");
                            //     $options = $pdo->prepare("INSERT INTO options (question_id, option_text, is_correct)
                            //  VALUES (:questionID, :optionText, :isCorrect)");
                            //     $options->bindParam(':questionID', $lastInsertID);
                            //     $options->bindParam(':optionText', $optionText);
                            //     $options->bindParam(':isCorrect', $isCorrect);
                            //     $options->execute();
                            $options =  query($pdo, "INSERT INTO options (question_id, option_text, is_correct) VALUES (?,?,?)", [$lastInsertID, $optionText, $isCorrect]);
                        }
                        if ($options->rowCount() > 0) {
                            response($_POST, 200, "Soru ve Cevaplar Başarıyla Eklendi", null, true);
                        } else {
                            response(null, 402, null, "Cevaplar Eklenemedi", false);
                        }
                    } else {
                        response(null, 402, null, "Zorunlu Alanları Doldurun!", false);
                    }
                } else {
                    response(null, 402, null, "Soru ve Cevaplar Eklenemedi", false);
                }
            } else {
                response(null, 402, null, "Zorunlu Alanları Doldurun!", false);
            }
        }
        $pdo->commit();
    } else {
        response(null, 406, null, "Geçersiz istek methodu", false);
    }
} catch (Exception $error) {
    response(null, 500, null, "Sunucu Hatası", false);
    $pdo->rollBack();
    die($error->getMessage());
}
