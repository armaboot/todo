<?php
// проверка на пустую запись в форму
require_once 'version1.php';
$name = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = test_input($_POST["name"]);
}
include 'version1.php';
function test_input($data) {
    return trim($data);

}

$expectedResult = test_input($name);
$actualResult = file('data.txt');
assert($expectedResult === $actualResult, 'Задача не была добавлена.');
echo "Тест на добавление задачи пройден.\n";


?>





