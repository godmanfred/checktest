<?php

require('functions.php');	// Файлик функций (не стал делать классом чтоб быстрее выполнить задание)

// intval - доп проверка для безопасности
if(isset($_POST['role_id']) && intval($_POST['role_id']))
{
	$con = DBConnect();
	
	$roleId = $_POST['role_id'];

	$allRolesRoute = []; // Так как каждая роль подразумевает включение всех предыдущих, соберём все в массив
	$decStr = '';
	while($roleId > 0)
	{
		$roleIdDec = $roleId - ($roleId * 2);
		$decStr .= "AND role_ids NOT LIKE '%$roleIdDec%' ";		// Дописываем проверки на исключение
		
		// Получим плоский массив данных из БД для построения дерева, учитывая зависимости и исключения
		$query = mysqli_query($con, "SELECT * FROM route WHERE role_ids LIKE '%$roleId%' ".$decStr." ORDER BY parent;"); 
		if($query)	$mas = mysqli_fetch_all($query);
		else
		{
			echo('Problem with SELECT FROM route table');
			die();
		}
		
		$allRolesRoute = array_merge($mas, $allRolesRoute);
		$roleId--;
	}

	// Удалить из массива одинаковые массивы
	$allRolesRoute = array_map("unserialize", array_unique(array_map("serialize", $allRolesRoute)));
	
	// Конструируем дерево
	$arrResult = constructTree($allRolesRoute);

	// Получаем html готового дерева 
	$arrExcl = [];		// Массив исключений вывода тут пустой
	$htmlTree = '';

	paintTree($arrResult, $arrExcl, $htmlTree);
	
	echo json_encode($htmlTree);
}
die();

?>