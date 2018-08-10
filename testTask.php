<?php
	require 'additionalFunc.php';

	$user=array(
		'USER_LOGIN'=>'YOUR_LOGIN', #Ваш логин (электронная почта)
	 	'USER_HASH'=>'YOUR_HASH' #Хэш для доступа к API (смотрите в профиле пользователя)
	);
	$subdomain = 'YOUR_SUBDOMAIN';
	// Авторизация
	$link='https://'.$subdomain.'.amocrm.ru/private/api/auth.php?type=json';
	$out = execRequestAmoCrm($link, $user);

	$Response=json_decode($out,true);
	$Response=$Response['response'];
	if(isset($Response['auth'])) #Флаг авторизации доступен в свойстве "auth"
	 echo 'Авторизация прошла успешно' . '<br><br>';
	else echo 'Авторизация не удалась' . '<br><br>';

	$stop = false; $limitOffset = 0;
	while (!$stop) {

		// Получение списка сделок
		$link='https://'.$subdomain.'.amocrm.ru/api/v2/leads?limit_rows=500&limit_offset='.$limitOffset;
		$out = execRequestAmoCrm($link);
		$Response=json_decode($out,true);
		$Response=$Response['_embedded']['items'];

		// Для количества сделок > 500
		$limitOffset += 500;
		if (count($Response) < 500) $stop = true; 

		$tasks['add'] = array();

		echo 'Задачи: ' . '<br>';
		foreach ($Response as $key => $value) {
			echo $value['name'];

			// Получение списка задач в сделке
			$link='https://'.$subdomain.'.amocrm.ru/api/v2/tasks?element_id='.$value['id'];
			$out = execRequestAmoCrm($link);
			$Response=json_decode($out,true);
			$Response=$Response['_embedded']['items'];

			// Если в сделке нет задач, то добавляем задачу в массив
			if (is_null($Response)) {
				$tasks['add'][] = array(
					'element_id' => $value['id'],
					'element_type' => 2,
					'text' => 'Сделка без задачи'
				);
				echo ' - Без задач';
			}
			echo '<br>';
		}

		echo '<br>';

		// Добавляем задачи, если таковые имеются
		if (count($tasks['add']) != 0) {
			$link='https://'.$subdomain.'.amocrm.ru/api/v2/tasks';
			$out = execRequestAmoCrm($link, $tasks);
			echo 'Задачи добавлены';
		} else {
			echo 'Все сделки имеют задачи';
		}
	}