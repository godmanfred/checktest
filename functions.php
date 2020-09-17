<?php

// Данные подключения к БД (для простоты расположу тут)
define("HOST", 'localhost');
define("USER", 'root');
define("PASS", '');
define("NAME", 'test');

// Подключение к БД
function DBConnect()
{
	return mysqli_connect(HOST, USER, PASS, NAME);;
}

/* Создание массива дерева по плоскому массиву - данным таблицы route
* @param $arrRoute - массив данных таблицы route
* @return древесный массив
*/
function constructTree($arrRoute)
{
	$arrResult = [];
	foreach($arrRoute as $key => $val)
	{
		// Сразу запишем корень, от которого будем отталкиваться
		if($key == 0 && $val[4] == 0)
		{
			$arrResult[$val[0]] = ['data' => $val, 'node' => []];
		}
		else
		{
			// Рекурсивный поиск родителя и запись текущего узла в него
			$find = false;
			getTree($val, $arrResult, $find);
		}
	}
	return $arrResult;
}

// Поиск вглубину и построение узлов
function getTree($val, &$arrResult, &$find)
{
	foreach($arrResult as $key => $data)
	{
		// Если нашли родителя - стоп
		if($key == $val[4])
		{
			$arrResult[$key]['node'][$val[0]] = ['data' => $val, 'node' => []];
			$find = true;
			break;
		}
		else
		{
			// Уходим глубже, если не достигли конца ветки - иначе к следующему элементу текущего уровня
			if($data['node'] !== [])
			{
				getTree($val, $arrResult[$key]['node'], $find); 		// Берём следующий по глубине уровень, чтобы точно так же искать уже в нём
				
				if($find)	break;	// Если нашли - прерываем дальнейший поиск на текущем уровне
			}
		}
	}
}

/* Построение дерева
* @param $arrResult - древесный массив
* @param $arrExcl - массив uri исключений 
* @param $htmlTree - html результирующего дерева
*/
function paintTree($arrResult, $arrExcl, &$htmlTree)
{
	$htmlTree .= '<ul>';
	
	foreach($arrResult as $key => $val)
	{
		// Проверка на исключения вывода
		if(!in_array($val['data'][3], $arrExcl))
		{
			$htmlTree .= '<li><a href="/'.$val['data'][3].'">'.$val['data'][2].'</a>';
			
			if($val['node'] !== [])
				paintTree($arrResult[$key]['node'], $arrExcl, $htmlTree);

			$htmlTree .= '</li>';
		}
	}
	
	$htmlTree .= '</ul>';
}


?>