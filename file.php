<?php

// Проверка версии PHP
if (version_compare(PHP_VERSION, '8.2.0', '<')) {
    die('Error: PHP 8.2 or higher is required.');
}

// Проверяем наличие необходимых расширений
if (!extension_loaded('imagick')) {
    die("Error: The Imagick extension is not installed on this server.");
}
if (!class_exists('ZipArchive')) {
    die("Error: The ZipArchive class is not available on this server.");
}

// Обработчик загрузки файла
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["fileUpload"])) {
    $uploadDir = "uploads/";
    $file = $_FILES["fileUpload"];
    $extension = pathinfo($file["name"], PATHINFO_EXTENSION);
    $randomName = bin2hex(random_bytes(6));
    $timestamp = time();
    $newFilename = $randomName . $timestamp . "." . $extension . ".zip";

    $zip = new ZipArchive();
    if ($zip->open($uploadDir . $newFilename, ZipArchive::CREATE) === TRUE) {
        $zip->addFile($file["tmp_name"], $randomName . $timestamp . "." . $extension);
        $zip->close();
        $response = "File is compressed and stored.";
    } else {
        $response = "Failed to compress the file.";
    }

    $fileUrl = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . "?file=" . $randomName . $timestamp . "." . $extension;
    $mimeType = mime_content_type($file["tmp_name"]);
    $displayLink = "<a href='#' onclick='copyLink(\"" . $fileUrl . "\")'>" . $fileUrl . "</a>";
    if (strpos($mimeType, 'image/') === 0) {
        $displayLink .= "<img src='" . $fileUrl . "' alt='Uploaded Image' style='margin-top: 10px;' width='60%' height='auto'>";
    } elseif (strpos($mimeType, 'video/') === 0) {
        $displayLink .= "<video width='320' height='240' controls><source src='" . $fileUrl . "' type='" . $mimeType . "'>Your browser does not support the video tag.</video>";
    } else {
        $displayLink .= "<a href='" . $fileUrl . "' target='_blank'>Download File</a>";
    }
    echo $response . "<br>" . $displayLink . "<br><div id='copyLinkMessage'></div>";
    exit;
}

// Обработчик отображения файла
if (isset($_GET['file'])) {
    $filename = $_GET['file'];
    $zipPath = "uploads/" . $filename . ".zip";

    if (file_exists($zipPath)) {
        $zip = new ZipArchive();
        if ($zip->open($zipPath)) {
            $tmpDir = "uploads/tmp/" . bin2hex(random_bytes(5)) . "/";
            if (!file_exists($tmpDir)) {
                mkdir($tmpDir, 0777, true);
            }
            $zip->extractTo($tmpDir);
            $zip->close();
            $filePath = $tmpDir . $filename;

            if (file_exists($filePath)) {
                $mimeType = mime_content_type($filePath);
                header("Content-Type: $mimeType");
                header("Content-Disposition: inline; filename=\"$filename\"");
                readfile($filePath);

                unlink($filePath);
                rmdir($tmpDir);

                exit;
            }
        }
    }
    echo "File not found.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./init/bootstrap.min.css">
    <title>File Upload</title>
    <link rel="icon" type="image/png" href="./init/favicon.png">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            margin: 10px;
        }
        #copyLinkMessage {
            position: fixed; /* Изменено на фиксированное позиционирование */
            top: 50%; /* Центрирование по вертикали */
            left: 50%; /* Центрирование по горизонтали */
            transform: translate(-50%, -50%); /* Настройка точного центрирования */
            background-color: white; /* Фоновый цвет */
            color: black; /* Цвет текста */
            padding: 10px; /* Внутренние отступы */
            border-radius: 5px; /* Закругление углов */
            display: none; /* Скрываем по умолчанию */
            z-index: 1050; /* Поверх других элементов */
        }
    </style>
</head>
<body class="bg-dark text-white">
    <div class="container text-center">
        <form id="uploadForm" enctype="multipart/form-data">
            <input type="file" class="form-control" name="fileUpload" id="fileInput" style="margin-bottom: 10px;">
            <button type="submit" class="btn btn-primary mt-3" id="uploadButton" disabled>Upload File</button>
        </form>
        <div id="progressBar" class="progress mt-3" style="height: 30px; display: none;">
            <div class="progress-bar" role="progressbar" style="width: 0%;">0%</div>
        </div>
        <div id="uploadResult"></div>
    </div>

    <script src="./init/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('fileInput').addEventListener('change', function() {
            var uploadButton = document.getElementById('uploadButton');
            uploadButton.disabled = !this.files.length;
        });

        document.getElementById('uploadForm').addEventListener('submit', function(event) {
            event.preventDefault();
            var formData = new FormData(this);
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "", true);

            xhr.upload.onprogress = function(event) {
                var percent = (event.loaded / event.total) * 100;
                var progressBar = document.querySelector('.progress-bar');
                document.getElementById('progressBar').style.display = 'block';
                progressBar.style.width = percent + '%';
                progressBar.textContent = Math.round(percent) + '%';
            };

            xhr.onload = function() {
                if (xhr.status == 200) {
                    document.getElementById('uploadResult').innerHTML = xhr.responseText;
                } else {
                    alert('Upload failed');
                }
            };

            xhr.send(formData);
        });

        function copyLink(url) {
            navigator.clipboard.writeText(url).then(function() {
                var messageBox = document.getElementById('copyLinkMessage');
                messageBox.textContent = 'Link copied!';
                messageBox.style.display = 'block';
                setTimeout(function() {
                    messageBox.style.display = 'none';
                }, 1500);
            }, function(err) {
                alert('Copy error: ' + err);
            });
        }
    </script>
</body>
</html>
