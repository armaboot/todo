<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>

<?php
    $filename = 'products.txt';

    if(file_exists($filename)) {
        $lines = file($filename);

        echo '<ul>';
        foreach ($lines as $line) {
            echo '<li>'.$line.'</li>';
        }
        echo '</ul>';
    } else {
        echo 'File not found';
    }
?>

</body>
</html>