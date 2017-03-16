<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__.'/../src/');
$dotenv->load();

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';

// Errores en archivo log o en pantalla si estamos en desarrollo
error_reporting(-1);
// Errores en archivo log o en pantalla si estamos en desarrollo
ini_set('ignore_repeated_source', 0);
ini_set('ignore_repeated_errors', 1); // do not log repeating errors
// source of error plays role in determining if errors are different
ini_set('log_errors', 1);
ini_set('error_log', __DIR__.'/../storage/logs/'.date('Y-m-d').'_error.log');
if ($settings['settings']['displayErrorDetails']) {
    ini_set('display_errors', 1); // Mostramos los errores en pantalla
    ini_set('display_startup_errors', 1);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
}

try {
    $opt = [
        \PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $db = new \PDO("{$settings['settings']['db']['driver']}:host={$settings['settings']['db']['host']};dbname={$settings['settings']['db']['database']};port={$settings['settings']['db']['port']};charset={$settings['settings']['db']['charset']}", $settings['settings']['db']['username'], $settings['settings']['db']['password'], $opt);
} catch (\PDOException $e) {
    die('Connect Error: '.$e->getMessage());
}

session_name($settings['settings']['session_name']);
session_start();
session_set_cookie_params(14400);
ini_set('session.gc_maxlifetime', 14400);
session_set_save_handler(New App\Session\SessionHandler($db, $settings['settings']['db']['session_table']), true);

$container = new \Slim\Container($settings);
$app = new \Slim\App($container);

// Set up containers
require __DIR__ . '/../src/containers.php';

// Register routes
require __DIR__ . '/../src/routes.php';

// Register middleware
require __DIR__ . '/../src/middlewares.php';

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Custom Validators
require __DIR__ . '/../src/validators.php';

// Run app
$app->run();
