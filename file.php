<?php
session_start();

// Очистка сессии если ответ правильный
if (isset($_SESSION['answer_correct']) && $_SESSION['answer_correct']) {
    session_destroy();
    session_start();  // Начинаем новую сессию после очистки
}

// Генерация новых случайных чисел каждый раз, когда страница загружается
$_SESSION['num1'] = rand(1, 10);
$_SESSION['num2'] = rand(1, 10);

// Проверка ответа пользователя
$correctAnswer = $_SESSION['num1'] + $_SESSION['num2'];
$userAnswer = filter_input(INPUT_POST, 'answer', FILTER_VALIDATE_INT);

$_SESSION['answer_correct'] = false;  // Инициализация статуса ответа как неверный
if ($userAnswer !== null && $userAnswer === $correctAnswer) {
    $_SESSION['answer_correct'] = true;  // Обновление статуса ответа на правильный
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File upload</title>
    <link href="./init/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="./init/favicon.png">
    <script src="./init/jquery.min.js"></script>
    <style>
        body {
            background-color: #343a40;
            color: #fff;
        }
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        h1 {
            text-align: center;
        }
        input[type="number"] {
            width: 80px;
            -webkit-appearance: none;  /* Добавлено для удаления стрелок в Chrome/Safari */
            -moz-appearance: textfield; /* Добавлено для удаления стрелок в Firefox */
        }
        input[type="number"]::-webkit-inner-spin-button, 
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        /* Стили для модального окна, чтобы оно открывалось на весь экран */
        .modal-dialog {
            max-width: 100%;
            width: 100vw; /* Ширина окна на весь экран */
            height: 100vh; /* Высота окна на весь экран */
            margin: 0;
            border: none;
        }
        
        .modal-content {
            height: 100vh; /* Высота контента на весь экран */
            border: none; /* Рамка в 1 пиксель */
        }
        
        iframe {
            border: none; /* Убираем рамку у iframe */
            width: 100%;
            height: 100%;
        }

    </style>

</head>
<body>
    <div class="container">
        <img src="./init/logo.png" alt="Logo" class="mb-4" width="60%" height="auto">
        <h1>What is <?php echo $_SESSION['num1'] . ' + ' . $_SESSION['num2']; ?>?</h1>
        <p>Enter the number to upload the file</p>
        <form method="POST" class="text-center">
            <input type="number" name="answer" id="answer" min="1" max="99" autofocus oninput="checkAnswer()" class="form-control mb-3" style="width: 80px; margin: 0 auto;">
        </form>

        <!-- Модальное окно для отображения file.php -->
        <div class="modal fade" id="resultModal" tabindex="-1" role="dialog" aria-labelledby="resultModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <iframe src="./file.php" width="100%" height="600"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="./init/bootstrap.min.js"></script>
    <script>
        function checkAnswer() {
            var userAnswer = document.getElementById('answer').value;
            var correctAnswer = <?php echo $correctAnswer; ?>;
    
            if (parseInt(userAnswer) === correctAnswer) {
                $('#resultModal').modal({
                    backdrop: 'static', // Опционально: делаем фон некликабельным
                    keyboard: false    // Опционально: блокируем закрытие модального окна клавишей ESC
                });
                $('#resultModal').modal('show'); // Показываем модальное окно
            }
        }
    </script>


</body>
</html>
