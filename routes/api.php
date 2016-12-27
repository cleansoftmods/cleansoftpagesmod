<?php use Illuminate\Routing\Router;

/**
 *
 * @var Router $router
 *
 */
$router->group(['prefix' => 'api'], function (Router $router) {
    $router->get('pages', function () {
        echo 'sss'; exit;
    });
});
