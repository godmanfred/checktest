<!doctype html>
<html lang="ru">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Пушкарь ДВ</title>
		
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
	<script src="script.js"></script>	
</head>
<body>

<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require('functions.php');	// Файлик функций (не стал делать классом чтоб быстрее выполнить задание)

// Подключимся к БД
if(!isset($con))
	$con = DBConnect();

//////////////// 6) Обработка адресов (роутинг)... В рамках задания сделаю попроще, тут (в htaccess перенаправление на index)...

$outputFlag = true;	// Флаг для вывода селекта с его деревьями (не нужно, если это не главная)

$url = parse_url('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
if(isset($url['path']) && $url['path'] != '/')
{
	$outputFlag = false;
	
	$pathParse = explode('/', $url['path']);
	$pathFirstLvl = $pathParse[1];
	
	$query = mysqli_query($con, "SELECT name FROM route WHERE uri='$pathFirstLvl'");
	if($query)	$mas = mysqli_fetch_all($query);
	else die('Problem with SELECT FROM route table');
}
else
{
	// В задании написано ДЛЯ КАЖДОЙ ССЫЛКИ - то есть и для корня-главной тоже...
	// Других ориентиров, кроме отсутствия пути у нас нет, если будут ещё пустые ссылки в БД, то выдастся первая (id 1 - это root)
	$query = mysqli_query($con, "SELECT name FROM route WHERE uri=''");
	if($query)	$mas = mysqli_fetch_all($query);
	else die('Problem with SELECT FROM route table');
}

echo '<h1>'.$mas[0][0].'</h1>';

// 1) Нам нужно дерево - для этого у каждого элемента должен быть свой родитель...
// Добавим колонку parent, где зададим id родителя или 0, если это корень
// ВСЁ ЭТО ДЕЛАЕТСЯ ЕДИНОЖДЫ В PMA ИЛИ МИГРАЦИЯМИ, НО Я ПОНЯЛ ТАК, ЧТО В РАМКАХ ЗАДАНИЯ НАДО ЗАПРОСАМИ
																	
// ТАБЛИЦА ROLE

// Колонка уже есть? (В MariaDB было бы просто 1 запросом через IF NOT EXISTS)
$query = mysqli_query($con, "SHOW COLUMNS FROM role where field = 'parent';");
if($query) $res = mysqli_fetch_all($query);

// Если нет добавим и заполним
if($res === [])
{
	if(!mysqli_query($con, "ALTER TABLE role ADD parent int(3) NOT NULL AFTER name")) 
		die('Problem with ALTER table ROLE on ADD parent');

	// Заполним родителей
	$q1 = mysqli_query($con, "UPDATE role SET parent = 0 WHERE id = 1");
	$q2 = mysqli_query($con, "UPDATE role SET parent = 1 WHERE id = 2");
	$q3 = mysqli_query($con, "UPDATE role SET parent = 2 WHERE id = 3");
	$q4 = mysqli_query($con, "UPDATE role SET parent = 2 WHERE id = 4");
	if(!$q1 || !$q2 || !$q3 || !$q4)	
		die('Problem with UPDATE table ROLE on SET parent');
}

// ТАБЛИЦА ROUTE

// Колонка уже есть?
$query = mysqli_query($con, "SHOW COLUMNS FROM route where field = 'parent';");
if($query) $res = mysqli_fetch_all($query);

// Если нет добавим и заполним
if($res === [])
{
	if(!mysqli_query($con, "ALTER TABLE route ADD parent int(3) NOT NULL AFTER uri")) 
		die('Problem with ALTER table ROUTE on ADD parent');

	// Заполним родителей
	$q1 = mysqli_query($con, "UPDATE route SET parent = 0 WHERE id = 1");
	$q2 = mysqli_query($con, "UPDATE route SET parent = 1 WHERE id = 2");
	$q3 = mysqli_query($con, "UPDATE route SET parent = 1 WHERE id = 3");
	$q4 = mysqli_query($con, "UPDATE route SET parent = 1 WHERE id = 4");
	$q5 = mysqli_query($con, "UPDATE route SET parent = 1 WHERE id = 5");
	$q6 = mysqli_query($con, "UPDATE route SET parent = 1 WHERE id = 6");
	$q7 = mysqli_query($con, "UPDATE route SET parent = 1 WHERE id = 7");
	$q8 = mysqli_query($con, "UPDATE route SET parent = 7 WHERE id = 8");
	$q9 = mysqli_query($con, "UPDATE route SET parent = 7 WHERE id = 9");
	if(!$q1 || !$q2 || !$q3 || !$q4 || !$q5 || !$q6 || !$q7 || !$q8 || !$q9)	
		die('Problem with UPDATE table ROUTE on SET parent');
}
	
////////////// 2) Нам нужны зависимости - это внешний ключ... Казалось бы, но там есть где несколько зависимостей и есть где запрет зависимости... Тогда строку сами посмотрим...
// ВСЁ ЭТО ДЕЛАЕТСЯ ЕДИНОЖДЫ В PMA ИЛИ МИГРАЦИЯМИ, НО Я ПОНЯЛ ТАК, ЧТО В РАМКАХ ЗАДАНИЯ НАДО ЗАПРОСАМИ
	
// Колонка уже есть?
$query = mysqli_query($con, "SHOW COLUMNS FROM route where field = 'role_ids';");
if($query) $res = mysqli_fetch_all($query);

// Если нет добавим
if($res === [])
{
	if(!mysqli_query($con, "ALTER TABLE route ADD role_ids TEXT NOT NULL AFTER id")) 
		die('Problem with ALTER table ROUTE on ADD role_ids');
	
	// Проставим правильные отношения (через запятую несколько, минус - отсутствие)
	$q1 = mysqli_query($con, "UPDATE route SET role_ids = '1' WHERE id = 1");
	$q2 = mysqli_query($con, "UPDATE route SET role_ids = '1,-2' WHERE id = 2");
	$q3 = mysqli_query($con, "UPDATE route SET role_ids = '2' WHERE id = 3");
	$q4 = mysqli_query($con, "UPDATE route SET role_ids = '2' WHERE id = 4");
	$q5 = mysqli_query($con, "UPDATE route SET role_ids = '2' WHERE id = 5");
	$q6 = mysqli_query($con, "UPDATE route SET role_ids = '3' WHERE id = 6");
	$q7 = mysqli_query($con, "UPDATE route SET role_ids = '3,4' WHERE id = 7");
	$q8 = mysqli_query($con, "UPDATE route SET role_ids = '4' WHERE id = 8");
	$q9 = mysqli_query($con, "UPDATE route SET role_ids = '3,4' WHERE id = 9");
	if(!$q1 || !$q2 || !$q3 || !$q4 || !$q5 || !$q6 || !$q7 || !$q8 || !$q9)	
		die('Problem with UPDATE table ROUTE on SET role_ids');
}

////////////////////////// 3) Вывод дерева

// Выводим записи, отсортированные по родителям, 0 - корень должен быть один и самой первой строкой
$query = mysqli_query($con, "SELECT * FROM route ORDER BY parent;");
if($query)	$mas = mysqli_fetch_all($query);
else die('Problem with SELECT FROM route table');

$arrResult = constructTree($mas);

// Вывод готового дерева 
// В задании написано, что оно должно быть на всех страницах, а так же видно по примеру, что не должны выводиться пункты Log-in Log-out

$arrExcl = ['login', 'logout'];		// Массив исключений вывода
$htmlTree = '';

paintTree($arrResult, $arrExcl, $htmlTree);

echo $htmlTree;

////////////////////////////// 4) Вывод select	

if($outputFlag)
{
	// Получить из БД роли и вывести
	$query = mysqli_query($con, "SELECT * FROM role");
	if($query) $mas = mysqli_fetch_all($query);
	else die('Problem with SELECT FROM role table');

	// Вывести select
	echo '<label for="role">Выберите роль</label>';
	echo '<br><br>';
	echo '<select id="role">
			<option value="0" selected>Не выбрано</option>';
	foreach($mas as $val)
		echo '<option value="'.$val[0].'">'.$val[1].'</option>';
	echo '</select>';
	echo '<br><br>';
	
	// Блок для вывода дерева
	echo '<div id="dynamic_tree"></div>';
}
									
?>

</body></html>