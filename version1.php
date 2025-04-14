<?php

$tasks = [];

//решил переписать немного логику и поэкспериментировать
//$taskInfo = 'Переменная tasks присутствует в POST-запросе';
//var_dump(filter_var($taskInfo, INPUT_POST, 'tasks'));

//if (filter_has_var(INPUT_POST, 'tasks')) {
//    echo "Переменная tasks присутствует в POST-запросе";
//} else {
//    echo "Переменная tasks отсутствует в POST-запросе";
//}

//
//if (session_id() == "") {
//    session_start();
//}
//$password = '';
//if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['form_name'] == 'task') {
//    $password = isset($_POST['password']) ? $_POST['password'] : '';
//    if ($password == 'user') {
//        $_SESSION['password'] = $password;
//    } else {
//        $password = isset($_SESSION['password']) ? $_SESSION['password'] : '';
//    }
//    if ($password != 'user') {
//        echo "\n";
//        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//            echo "Пароль неверный\n";
//        }
//    } else {
//            echo "Пароль установлен";
//    }
//}















// Чтение списка задач
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (file_exists('data.txt')) {
            $lines = file('data.txt');
            if ($lines !== false) {
                $tasks = array_map('trim', $lines);
                //решил переписать немного логику и поэкспериментировать
                $taskInfo = 'Переменная tasks присутствует в POST-запросе';
                var_dump(filter_var($taskInfo, INPUT_POST, 'tasks'));
            }
        }
        break;
    case 'POST':
        // Обработка POST-запросов
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Читаем текущие задачи из файла
            $currentTasks = file_exists('data.txt') ? array_map('trim', file('data.txt')) : [];

            // Добавление новой задачи, если она не пустая
            if (isset($_POST['task']) && !empty(trim($_POST['task']))) {
                $newTask = trim($_POST['task']);

                // Объединяем старые задачи с новой задачей
                $tasks = array_merge($currentTasks, [$newTask]);
            }
            file_put_contents('data.txt', implode("\n", array_values($tasks)));
            // Ответ для AJAX-запроса
            if (isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
                echo json_encode(['status' => 'ok']); // Отправляем ответ для AJAX
                exit; // Завершаем выполнение скрипта
            }
        }
        break;
    case 'PUT':
        parse_str(file_get_contents("php://input"), $put_vars);

        // Обновление существующей задачи
        if (isset($put_vars['update_task']) && isset($put_vars['index'])) {
            $updatedTask = trim($put_vars['update_task']);
            $index = intval($put_vars['index']);
            $tasks[$index] = $updatedTask;

            file_put_contents('data.txt', implode("\n", array_values($tasks)));

            if (isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
                echo json_encode(['status' => 'ok']);
                exit;
            }

        }
        break;
    case 'DELETE':
        parse_str(file_get_contents("php://input"), $delete_data);

        // Удаление задачи по индексу
        if (isset($delete_data['task_index']) && is_numeric($delete_data['task_index'])) {
            $taskIndex = intval($delete_data['task_index']);

            // Читаем текущие задачи из файла
            $currentTasks = file_exists('data.txt') ? array_map('trim', file('data.txt')) : [];

            // Удаление задачи по индексу
            if (isset($currentTasks[$taskIndex])) {
                unset($currentTasks[$taskIndex]);

                // Сохраняем оставшиеся задачи в файл
                file_put_contents('data.txt', implode("\n", array_values($currentTasks)));

                // Ответ для AJAX-запроса
                if (isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
                    echo json_encode(['status' => 'ok', 'message' => 'Задача удалена']); // Отправляем ответ для AJAX
                    exit; // Завершаем выполнение скрипта
                }
            } else {
                // Задача с таким индексом не найдена
                echo json_encode(['status' => 'error', 'message' => 'Задача с данным индексом не найдена']);
            }
        } else {
            // Индекс задачи не передан или некорректен
            echo json_encode(['status' => 'error', 'message' => 'Необходимо указать корректный индекс задачи']);
        }
        break;
    default:
        // Код для обработки неизвестных методов - Методо не разрешен
        http_response_code(405);
        echo "Метод запроса не поддерживается.";
        break;
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
            <form method="post">
                <fieldset>
                    <label>
                        Введите задачу
                        <textarea
                                type="text"
                                name="task"
                                autofocus
                                placeholder="Добавить новую задачу..."
                                aria-label="Professional short bio"
                                id="task"
                        >
                        </textarea>
                    </label>
                </fieldset>
                <input
                        type="submit"
                        value="Добавить"
                />
            </form>
            <?php if ($tasks): ?>
                <?php foreach ($tasks as $key => $task): ?>
                    <div style="margin: 10px" class="task-item">
                        <span><?= htmlspecialchars($task) ?></span>
                        <small>(ID: <?= $key + 1 ?>)</small>
                        <form method="post" style="display: inline-block; margin-left: 10px;" class="delete-task-form">
                            <button name="delete" value="<?=$key?>" class="delete-task">x</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Нет задач.</p>
            <?php endif; ?>
        </div>
        <div>

        </div>


    </div>
</main>

<script>
    // нерабочий говнокод
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

