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
    // Добавление задачи, если она не пустая
    if (isset($_POST['task']) && !empty(trim($_POST['task']))) {
        $tasks[] = trim($_POST['task']);
    }

    // Удаление задачи по индексу
    if (isset($_POST['delete']) && isset($tasks[$_POST['delete']])) {
        array_splice($tasks, $_POST['delete'], 1);
    }

    // Сохраняем новые данные в файл
    file_put_contents('data.txt', implode("\n", array_values($tasks)));
}
?>

<!doctype html>
<html lang="en">
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
        <div>
<!--            <button class="outline">Search</button>-->
        </div>
<!--        <div></div>-->

    </div>
</main>


</body>
</html>