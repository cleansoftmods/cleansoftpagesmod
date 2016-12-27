<?php use Illuminate\Routing\Router;

/**
 *
 * @var Router $router
 *
 */

$adminRoute = config('webed.admin_route');

$moduleRoute = 'pages';

/*
 * Admin route
 * */
$router->group(['prefix' => $adminRoute . '/' . $moduleRoute], function (Router $router) use ($adminRoute, $moduleRoute) {
    $router->get('/', 'PageController@getIndex')
        ->name('admin::pages.index.get')
        ->middleware('has-permission:view-pages');

    $router->post('/', 'PageController@postListing')
        ->name('admin::pages.index.post')
        ->middleware('has-permission:view-pages');

    $router->post('update-status/{id}/{status}', 'PageController@postUpdateStatus')
        ->name('admin::pages.update-status.post')
        ->middleware('has-permission:edit-pages');

    $router->get('create', 'PageController@getCreate')
        ->name('admin::pages.create.get')
        ->middleware('has-permission:create-pages');

    $router->get('edit/{id}', 'PageController@getEdit')
        ->name('admin::pages.edit.get')
        ->middleware('has-permission:edit-pages');

    $router->post('edit/{id}', 'PageController@postEdit')
        ->name('admin::pages.edit.post')
        ->middleware('has-permission:edit-pages');

    $router->delete('/{id}', 'PageController@deleteDelete')
        ->name('admin::pages.delete.delete')
        ->middleware('has-permission:delete-pages');
});

/**
 * Front site
 */
$router->get('/{slug?}', 'Front\ResolvePagesController@handle')
    ->where('slug', '[-A-Za-z0-9]+')
    ->name('front.web.resolve-pages.get');
