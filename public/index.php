<?php
declare(strict_types=1);

/**
 * Front Controller — entry point cho mọi HTTP request
 *
 * .htaccess sẽ rewrite tất cả request về file này.
 * App::run() lo phần còn lại: autoload, config, session, routing.
 */

require dirname(__DIR__) . '/app/Core/App.php';

App\Core\App::run();
