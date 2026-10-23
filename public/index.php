<?php
namespace Config;

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
chdir(FCPATH);
define('ROOTPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('APPPATH', ROOTPATH . 'app' . DIRECTORY_SEPARATOR);
define('WRITEPATH', ROOTPATH . 'writable' . DIRECTORY_SEPARATOR);

require ROOTPATH . 'vendor/autoload.php';
$paths = new Paths();
require $paths->systemDirectory . '/Boot.php';
exit(\CodeIgniter\Boot::bootWeb($paths));
