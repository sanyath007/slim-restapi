<?php

$container = $app->getContainer();

$container['errorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {
        return $response
                ->withStatus(500)
                ->withHeader("Content-Type", "application/json")
                ->write($exception->getMessage());
    };
};

// $capsule = new Illuminate\Database\Capsule\Manager;
// $capsule->addConnection($container['settings']['db']);
// $capsule->addConnection($container['settings']['db_hos'], 'hos');
// $capsule->addConnection($container['settings']['db_person'], 'person');
// $capsule->setAsGlobal();
// $capsule->bootEloquent();

// $container['db'] = function($c) use ($capsule) {
//     return $capsule;
// };

$container['db'] = function ($c) {
	try {
		$db = $c['settings']['db'];
		$options = [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		];

		return new PDO("mysql:host=" .$db['host']. ";dbname=" .$db['database']. "; charset=utf8", $db['username'], $db['password'], $options);
	} catch (Exception $e) {
		return $e->getMessage();
	}
};

// $container['auth'] = function($c) {
//     return new App\Auth\Auth;
// };

$container['logger'] = function($c) {
    $logger = new Monolog\Logger('My_logger');
    $file_handler = new Monolog\Handler\StreamHandler('../logs/app.log');
    $logger->pushHandler($file_handler);

    return $logger;
};

// $container['validator'] = function($c) {
//     return new App\Validations\Validator;
// };

// $container['jwt'] = function($c) {
//     return new StdClass;
// };

// $app->add(new Slim\Middleware\JwtAuthentication([
//     "path"          => '/api',
//     "logger"        => $container['logger'],
//     "passthrough"   => ["/test"],
//     "secret"        => getenv("JWT_SECRET"),
//     "callback"      => function($req, $res, $args) use ($container) {
//         $container['jwt'] = $args['decoded'];
//     },
//     "error"         => function($req, $res, $args) {
//         $data["status"] = "0";
//         $data["message"] = $args["message"];
//         $data["data"] = "";
        
//         return $res
//                 ->withHeader("Content-Type", "application/json")
//                 ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
//     }
// ]));

$container['UserController'] = function ($c) {
    return new App\Controllers\UserController($c);
};

$container['EmployeeController'] = function ($c) {
    return new App\Controllers\EmployeeController($c);
};

$container['CheckinController'] = function ($c) {
    return new App\Controllers\CheckinController($c);
};
