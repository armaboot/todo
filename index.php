<?php
// Инициализация переменной $tasks
$tasks = [];

// Чтение списка задач только при методе GET
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    if (file_exists('data.txt')) {
        $lines = file('data.txt');
        if ($lines !== false) {
            $tasks = array_map('trim', $lines);
        }
    }
}

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

    // Удаление задачи по индексу
    if (isset($_POST['delete']) && isset($tasks[$_POST['delete']])) {
        array_splice($tasks, $_POST['delete'], 1);
    }

    // Сохраняем обновленные данные в файл
    file_put_contents('data.txt', implode("\n", array_values($tasks)));

    // Ответ для AJAX-запроса
    if (isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
        echo json_encode(['status' => 'ok']); // Отправляем ответ для AJAX
        exit; // Завершаем выполнение скрипта
    }
}
?>

<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link
            rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css"
    >
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
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll('.delete-task').forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.preventDefault();

                var taskId = this.value;
                var taskDiv = this.closest('.task-item');


                var xhr = new XMLHttpRequest();
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