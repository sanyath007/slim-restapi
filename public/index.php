<?php
require('../vendor/autoload.php');
include('../config.php');

$app = new Slim\App(['settings' => $config]);

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
    return new \App\Controllers\UserController($c);
};

$container['EmployeeController'] = function ($c) {
    return new \App\Controllers\EmployeeController($c);
};

$container['CheckinController'] = function ($c) {
    return new \App\Controllers\CheckinController($c);
};

$app->options('./{routes:.+}', function ($req, $res) {
	return $res;
});

$app->add(function ($req, $res, $next) {

	if ($req->getMethod() !== 'OPTIONS') {
		$response = $next($req, $res);

		return $response
				->withHeader('Access-Control-Allow-Origin', '*')
				->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
				->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
	}

	$res = $res->withHeader('Access-Control-Allow-Origin', '*');
	$res = $res->withHeader('Access-Control-Allow-Methods', $req->getHeaderLine('Access-Control-Request-Method'));
	$res = $res->withHeader('Access-Control-Allow-Headers', $req->getHeaderLine('Access-Control-Request-Headers'));

	return $next($req, $res);
});

/** =============== ROUTES =============== */
$app->get('/user/{cid}', 'UserController:user');

$app->get('/info/{cid}', 'EmployeeController:info');
$app->get('/employee/{cid}', 'EmployeeController:employee');
$app->get('/employees', 'EmployeeController:employeeList');
$app->post('/employee-add', 'EmployeeController:employeeAdd');
$app->put('/employee-update/{cid}', 'EmployeeController:employeeUpdate');
$app->delete('/employee-del/{cid}', 'EmployeeController:employeeDel');
$app->get('/positions', 'EmployeeController:positionList');

$app->get('/checkin/{date}', 'CheckinController:checkinList');
$app->post('/checkin', 'CheckinController:checkin');
$app->post('/upload', 'CheckinController:upload');
$app->post('/avatar', 'CheckinController:avatar');
$app->get('/timein-img/{data}', 'CheckinController:timeinImg');

/** use this route if page not found. */
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/routes:.+', function ($req, $res) {
	$handler = $this->notFoundHandler; //using default slim page not found handler.
	return $handler($req, $res);
});
/** =============== ROUTES =============== */

$app->run();