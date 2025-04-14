<?php
    $tasks = file_exists('data.txt') ? array_map('trim', file('data.txt')) : [];
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        if (!empty($_POST['task'])) $tasks[] = $_POST['task'];
        if (isset($_POST['delete'])) array_splice($tasks, $_POST['delete'], 1);
        file_put_contents('data.txt', implode("\n", array_values($tasks)));
    }

//////////    Тренировка работы с CSV форматом  //////////////
//    $handle = fopen("test.csv", "r");
//    while (($row = fgetcsv($handle)) !== FALSE) {
//        print_r($row);
//    }
//    fclose($handle);

    $file = __DIR__ . '/test.csv';
    $rows = array_map('trim', file($file));
    array_shift($rows);

    foreach ($rows as $index => $row) {
        $params = array_map('trim', explode(',', $row));
//    $id = $params[0];
//    $login = $params[1];
//    $age = $params[2];
        list($id, $login, $age) = $params;

    }
?>

    <!doctype html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
        <title>TODOArma</title>
    </head>
    <body>

    <main class="container-fluid">
        <div class="grid">
            <div></div>
            <div>
                <form method="post"
                <fieldset>
                    <label>
                        TODOARMA
                    <input type="text" name="task" autofocus placeholder="Добавить новую задачу...">
                    </label>
                    <button type="submit">Добавить</button>
                </fieldset>
                </form>
                <?php if ($tasks): ?>
                <?php foreach ($tasks as $key => $task): ?>
                    <div style="margin: 10px">
                        <?= htmlspecialchars($task) ?>
                        <form method="post" style="display: inline-block; margin-left: 10px;">
                            <button name="delete" value="<?=$key?>">x</button>
                        </form>
                    </div>
                <?php endforeach; ?>
                <?php else: ?>
                    <p>Нет задач.</p>
                <?php endif; ?>
            </div>
            <div></div>
        </div>
    </main>

    <script>
        $.ajax({
            url: 'index.php',
            method: 'POST',
            data: { user_text: $('#task').val() },
            dataType: 'json', // Указываем, что ожидаем JSON-ответ
            success: function(response) {
                if (response.status === 'error') {
                    alert(response.message); // Показываем всплывающее окно с сообщением об ошибке
                } else {
                    // Обрабатываем успешный ответ
                    console.log("Текст прошел валидацию:", response);
                }
            }
        });
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll('.delete-task').forEach(function(button) {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    let taskId = this.value;
                    let taskDiv = this.closest('.task-item');
                    let xhr = new XMLHttpRequest();
                    xhr.open('POST', window.location.href, true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                            taskDiv.style.display = 'none';
                        }
                    };
                    xhr.send('delete=' + encodeURIComponent(taskId) + '&ajax=true');
                });
            });
        });
    </script>

    </body>
    </html>


