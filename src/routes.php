<?php

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

$app->post('/checkin', 'CheckinController:checkin');
$app->post('/upload', 'CheckinController:upload');
$app->post('/avatar', 'CheckinController:avatar');
$app->get('/timein-img/{data}', 'CheckinController:timeinImg');
$app->get('/checkin-all/{month}', 'CheckinController:checkinAll');
$app->get('/checkin-score/{month}', 'CheckinController:checkinScore');
$app->get('/checkin-time/{month}', 'CheckinController:checkinTime');
$app->get('/checkin/{date}', 'CheckinController:checkinList');
$app->get('/checkin-chart/{date}', 'CheckinController:checkinChart');

/** use this route if page not found. */
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/routes:.+', function ($req, $res) {
	$handler = $this->notFoundHandler; //using default slim page not found handler.
	return $handler($req, $res);
});
/** =============== ROUTES =============== */
