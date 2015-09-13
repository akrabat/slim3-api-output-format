<?php
// All file paths relative to root
chdir(dirname(__DIR__));
require "vendor/autoload.php";


// --------------------------------------------------------------------------
// Set up dependencies
// --------------------------------------------------------------------------
$container = new \Slim\Container();

$container['renderer'] = function ($c) {
    return new App\Renderer($c->get('request'));
};

// --------------------------------------------------------------------------
// Create App
// --------------------------------------------------------------------------
$app = new \Slim\App($container);


$app->get('/', function ($request, $response, $args) {

    $data = [
        'items' => [
            [
                'name' => 'Alex',
                'is_admin' => true,
            ],
            [
                'name' => 'Robin',
                'is_admin' => false,
            ],
        ],
    ];

    $response = $this->renderer->render($response, $data);
    $response->withStatus(200);
    return $response;
});


$app->run();
