<?php

try {
    $pdo = 'mysql:dbname=todo;host=127.0.0.1;port=3333';

    $pdo = 'mysql:dbname=testdb;host=127.0.0.1';
    $user = 'dbuser';
    $password = 'dbpass';

    $pdo = new PDO($pdo, $user, $password);

    //$pdo = new PDO('sqlite:todo.db');
   // $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Создание таблицы, если она еще не существует
    $createTableQuery = "
        CREATE TABLE IF NOT EXISTS tasks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            text TEXT NOT NULL
        )
    ";

    $pdo->exec($createTableQuery);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

$tasks = [];

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $selectQuery = "SELECT * FROM tasks ORDER BY id ASC"; // Запрашиваем все задачи, отсортированные по ID
        $stmt = $pdo->prepare($selectQuery);
        $stmt->execute();

        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC); // Получаем результат в виде ассоциативного массива
        break;

    case 'POST':
        if (isset($_POST['task']) && !empty(trim($_POST['task']))) {
            $newTask = trim($_POST['task']);

            // Добавляем новую задачу в базу данных
            $insertQuery = "INSERT INTO tasks (text) VALUES (:text)";
            $stmt = $pdo->prepare($insertQuery);
            $stmt->bindValue(':text', $newTask, PDO::PARAM_STR);
            $stmt->execute();

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

            // Обновляем задачу в базе данных
            $updateQuery = "UPDATE tasks SET text = :text WHERE id = :id";
            $stmt = $pdo->prepare($updateQuery);
            $stmt->bindValue(':text', $updatedTask, PDO::PARAM_STR);
            $stmt->bindValue(':id', $index, PDO::PARAM_INT);
            $stmt->execute();

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

            // Удаляем задачу из базы данных
            $deleteQuery = "DELETE FROM tasks WHERE id = :id";
            $stmt = $pdo->prepare($deleteQuery);
            $stmt->bindValue(':id', $taskIndex, PDO::PARAM_INT);
            $stmt->execute();

            // Ответ для AJAX-запроса
            if (isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
                echo json_encode(['status' => 'ok', 'message' => 'Задача удалена']); // Отправляем ответ для AJAX
                exit; // Завершаем выполнение скрипта
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
                        <span><?= htmlspecialchars($task['text']) ?></span>
                        <small>(ID: <?= $task['id'] ?>)</small>
                        <form method="post" style="display: inline-block; margin-left: 10px;" class="delete-task-form">
                            <button name="delete" value="<?=$task['id']?>" class="delete-task">x</button>
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