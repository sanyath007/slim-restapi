<?php

$container = $app->getContainer();

$container['db'] = function ($c) {
	try {
		$db = $c['settings']['db'];
		$options = [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		];

		return new PDO("mysql:host=" .$db['host']. ";dbname=" .$db['dbname']. "; charset=utf8", $db['username'], $db['password'], $options);
	} catch (Exception $e) {
		return $e->getMessage();
	}
};

$container['UserController'] = function ($c) {
    return new App\Controllers\UserController($c);
};

$container['EmployeeController'] = function ($c) {
    return new App\Controllers\EmployeeController($c);
};

$container['CheckinController'] = function ($c) {
    return new App\Controllers\CheckinController($c);
};
