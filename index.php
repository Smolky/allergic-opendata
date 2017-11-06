<?php

/**
 * AllergyLESS - Open Data Stractor
 *
 *
 * @author José Antonio García Díaz <joseantonio.garcia8@um.es>
 *
 * @package AllergyLESS
 */


// Require configuration
require 'config.php';

 
// Constants
define ('PRODUCTION', $production);
define ('BASE_URL', $base_url);
define ('VERSION', 0.1);


// Set the error level based on the stage
if (PRODUCTION) {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    ini_set ('display_errors', 1);
    ini_set ('display_startup_errors', 1);
    error_reporting (E_ALL); 
}


// Require vendor
require 'vendor/autoload.php';


// Dependency container
$container = new Pimple\Container();


// Require core libs
require ('core/BaseController.php');
require ('core/Item.php');
require ('core/Repo.php');
require ('core/Response.php');
require ('core/JSONResponse.php');
require ('core/Database.php');


// Require custom libs
require ('custom/functions.php');


// Database connection
// Database info is stored at config.php
$database = new Database ($dsn, $user, $password);
$container['connection'] = $database;
$container['pdo'] = $database->connect ();


// Template system
Twig_Autoloader::register();


// Create and configure the template system
// @link http://whateverthing.com/blog/2015/02/17/twig-tips-configuring-cache/
$twig_configuration = array ();
if (PRODUCTION) {
    $twig_configuration = array (
        'cache' => __DIR__ . '/cache/templates',
        'auto_reload' => true
    );
}

$loader = new Twig_Loader_Filesystem ("templates");
$twig = new Twig_Environment ($loader, $twig_configuration);


// Add global variables to the template
$twig->addGlobal ('base_url', BASE_URL);
$twig->addGlobal ('version', PRODUCTION ? VERSION : rand (1, 10000));


// Store the template system as a service
$container['loader'] = $loader;
$container['templates'] = $twig;


// Configure the transform layer
$transport = Swift_SmtpTransport::newInstance ($email_server, $email_port, $email_protocol)
    ->setUsername ($email_username)
    ->setPassword ($email_password)
    ->setStreamOptions (array ('ssl' => array ('allow_self_signed' => true, 'verify_peer' => false)))
;

$container['transport'] = $transport;


// Router
$router = new AltoRouter();
$router->setBasePath (ltrim (BASE_URL, '/'));

$container['router'] = $router;


session_start();


// Attach routers
// OpenWeather
$router->map ('GET', '/openweather', function () {

    
    // Load the classifier page
    require __DIR__ . '/controllers/openweather/OpenWeather.php';
    return new OpenWeather ();
    
});


// AEMED
$router->map ('GET', '/aemed', function () {

    // Load the classifier page
    require __DIR__ . '/controllers/aemed/Aemed.php';
    return new Aemed ();
    
});


// APIXU
$router->map ('GET', '/apixu', function () {

    // Load the classifier page
    require __DIR__ . '/controllers/apixu/Apixu.php';
    return new Apixu ();
    
});


// match current request URL
$match = $router->match();


// Determine which controller will handle the current route
if ($match && is_callable ($match['target'])) {
    $controller = call_user_func_array ($match['target'], $match['params']);
    
} else {

    // No controller was found, using a 404 controller
    require __DIR__ . '/controllers/maintenance/NotFound404.php';
    $controller = new NotFound404 ();
    
}


// Handle the controller
echo $controller->handle ();
