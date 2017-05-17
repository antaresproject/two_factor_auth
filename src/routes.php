<?php

/**
 * Part of the Antares package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    Two factor auth
 * @version    0.9.0
 * @author     Antares Team
 * @license    BSD License (3-clause)
 * @copyright  (c) 2017, Antares
 * @link       http://antaresproject.io
 */
use Antares\Routing\Router;

/* @var $router Router */

$router->group(['prefix' => 'two_factor_auth'], function (Router $router) {
    $router->get('configuration/area/{area}/provider/{provider}/edit', 'ConfigurationController@edit')->name('two_factor_auth.configuration.edit');
    $router->post('configuration/update', 'ConfigurationController@update')->name('two_factor_auth.configuration.update');

    $router->get('area/{area}/configuration', 'UserConfigurationController@getConfiguration')->name('two_factor_auth.get.configuration');
    $router->any('area/{area}/verify', 'AuthController@getVerify')->name('two_factor_auth.get.verify');
    $router->post('area/{area}/verify/check', 'AuthController@postVerify')->name('two_factor_auth.post.verify');

    $router->get('user/{user}/reset', 'UsersController@getReset')->name('two_factor_auth.user.reset');
    $router->get('user/{user}/area/{area}/enable', 'UserConfigurationController@enable')->name('two_factor_auth.user.configuration.enable');
    $router->get('user/{user}/area/{area}/disable', 'UserConfigurationController@disable')->name('two_factor_auth.user.configuration.disable');
    $router->match(['GET', 'POST'], 'user/area/{area}/configuration/save', 'UserConfigurationController@postConfiguration')->name('two_factor_auth.user.post.configuration');
    $router->any('area/{area}/cancel', 'AuthController@cancel')->name('two_factor_auth.get.cancel');
});
