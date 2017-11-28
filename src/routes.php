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
    $router->post('configuration/update', ['as' => 'two_factor_auth.configuration.update', 'uses' => 'ConfigurationController@update']);
    $router->get('configuration/area/{area}/provider/{provider}/edit', ['as' => 'two_factor_auth.configuration.edit', 'uses' => 'ConfigurationController@edit']);


    $router->get('area/{area}/configuration', ['as' => 'two_factor_auth.get.configuration', 'uses' => 'UserConfigurationController@getConfiguration']);
    $router->any('area/{area}/verify', ['as' => 'two_factor_auth.get.verify', 'uses' => 'AuthController@getVerify']);
    $router->post('area/{area}/verify/check', ['as' => 'two_factor_auth.post.verify', 'uses' => 'AuthController@postVerify']);

    $router->get('user/{user}/reset', ['as' => 'two_factor_auth.user.reset', 'uses' => 'UsersController@getReset']);
    $router->get('user/{user}/area/{area}/enable', ['as' => 'two_factor_auth.user.configuration.enable', 'uses' => 'UserConfigurationController@enable']);
    $router->get('user/{user}/area/{area}/disable', ['as' => 'two_factor_auth.user.configuration.disable', 'uses' => 'UserConfigurationController@disable']);
    $router->match(['GET', 'POST'], 'user/area/{area}/configuration/save', ['as' => 'two_factor_auth.user.post.configuration', 'uses' => 'UserConfigurationController@postConfiguration']);
    $router->any('area/{area}/cancel', ['as' => 'two_factor_auth.get.cancel', 'uses' => 'AuthController@cancel']);
});