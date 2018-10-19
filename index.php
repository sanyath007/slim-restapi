<?php
require('vendor/autoload.php');
include('config.php');

$app = new Slim\App(['settings' => $config]);

$container = $app->getContainer();

$container['db'] = function ($c) {
	try {
		$db = $c['settings']['db'];
		$options = [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		];

		return new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'], $db['username'], $db['password'], $options);
	} catch (Exception $e) {
		return $e->getMessage();
	}
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
$app->get('/user/{cid}', function ($req, $res) {
	try {
		$cid = $req->getAttribute('cid');
		$conn = $this->db;

		$sql = "SELECT e.*, p.position_name 
				FROM employees e 
				LEFT JOIN positions p ON (e.position_id=p.id) 
				WHERE (emp_id=:cid)";

		$pre = $conn->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
		$values = [':cid' => $cid];

		$pre->execute($values);
		$result = $pre->fetch();

		if ($result) {
			return $res->withJson([
				'employee' => [
					'cid' => $result['emp_id'],
					'fullName' => $result['prefix'] . $result['emp_fname']. ' ' .$result['emp_lname'],
					'position' => $result['position_name']
				]
			], 200);
		} else {
			return $res->withJson([
				$result
			]);
		}		
	} catch (Exception $e) {
		return $res->withJson([
			'error' => $e->getMessage()
		], 442);
	}
});

$app->post('/checkin', function ($req, $res) {
	try {
		$conn = $this->db;
		
		$sql = "INSERT INTO checkin (emp_id, checkin_date, timein, created_at, updated_at)VALUES(?,?,?,?,?)";
		
		$pre = $conn->prepare($sql, [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]);
		$values = [
			$req->getParam('emp_id'), 
			$req->getParam('checkin_date'), 
			$req->getParam('timein'), 
			date('Y-m-d H:i:s'), 
			date('Y-m-d H:i:s')
		];

		if ($pre->execute($values)) {
			return $res->withJson([
				'status' =>'success',
				'timein' => date('Y-m-d H:i:s')
			], 200);
		} else {
			return $res->withJson([
				'status' => 'error'
			], 422);
		}
	} catch (Exception $e) {
		return $res->withJson([
			'status' => 'error',
			'message' => $e->getMessage()
		], 442);
	}
});

/** use this route if page not found. */
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/routes:.+', function ($req, $res) {
	$handler = $this->notFoundHandler; //useing default slim page not found handler.
	return $handler($req, $res);
});
/** =============== ROUTES =============== */

$app->run();