<?php

/**
 * Part of the Antares Project package.
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
 * @copyright  (c) 2017, Antares Project
 * @link       http://antaresproject.io
 */

namespace Antares\TwoFactorAuth;

use Antares\Foundation\Events\SecurityFormSubmitted;
use Antares\TwoFactorAuth\Http\Handlers\SecuritySection;
use Antares\TwoFactorAuth\Listeners\SecurityFormListener;
use Antares\Foundation\Support\Providers\ModuleServiceProvider;
use Antares\TwoFactorAuth\Http\Handlers\ResetUserConfig;
use Antares\TwoFactorAuth\Http\Handlers\UserConfig;
use Antares\TwoFactorAuth\Services\TwoFactorProvidersService;
use Antares\TwoFactorAuth\Contracts\ProvidersRepositoryContract;
use Antares\TwoFactorAuth\Http\Middleware\TwoFactorAuthMiddleware;
use Illuminate\Auth\Events\Logout as LogoutEvent;
use Antares\Acl\RoleActionList;
use Illuminate\Routing\Router;
use Antares\Acl\Action;
use Antares\Model\Role;
use Event;

class TwoFactorAuthServiceProvider extends ModuleServiceProvider
{

    /**
     * The application or extension namespace.
     *
     * @var string|null
     */
    protected $namespace = 'Antares\TwoFactorAuth\Http\Controllers\Admin';

    /**
     * The application or extension group namespace.
     *
     * @var string|null
     */
    protected $routeGroup = 'antares/two_factor_auth';

    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'antares.form: users'         => ResetUserConfig::class,
        'antares.form: user.profile'  => UserConfig::class,
        'antares.form: security_form' => SecuritySection::class,
        SecurityFormSubmitted::class  => [
            SecurityFormListener::class,
        ],
    ];

    /**
     * registering component
     */
    public function register()
    {
        parent::register();
        $this->bindContracts();
        $this->app->singleton(TwoFactorProvidersService::class);
    }

    public function boot(Router $router)
    {
        parent::boot($router);

        $twoFaProviderService = $this->app->make(TwoFactorProvidersService::class);
        $providers            = config('antares/two_factor_auth::providers', []);

        foreach ($providers as $item) {
            if (isset($item['contract']) AND $item['contract'] !== $item['class']) {
                $this->app->bind($item['contract'], $item['class']);
            }
            $this->app->singleton($item['provider']);
            $twoFaProviderService->addProviderGateway($this->app->make($item['provider']));
        }

        $router->bind('provider', function($value) {
            return $this->app->make(ProvidersRepositoryContract::class)->findById($value);
        });

        if (config('antares/two_factor_auth::enabled')) {
            $router->pushMiddlewareToGroup('web', TwoFactorAuthMiddleware::class);
        }

        Event::listen(LogoutEvent::class, function() use($twoFaProviderService) {
            $twoFaProviderService->getAuthStore()->unverify();
        });

        publish('two_factor_auth', 'assets.scripts');
        listen('datatables:admin/control/users/index:after.action.edit', function($actions, $row) {
            $html = app('html');
            $actions->push($html->link(handles("antares::two_factor_auth/user/{$row->id}/reset"), trans('antares/two_factor_auth::users.reset_two_factor_auth'), [
                        'class'            => 'triggerable confirm',
                        'data-icon'        => 'info',
                        'data-title'       => trans("Are you sure?"),
                        'data-description' => trans('antares/two_factor_auth::users.reset_two_factor_auth_user_description', ['fullname' => $row->fullname])
            ]));
        });
    }

    /**
     * @return RoleActionList
     */
    public static function acl()
    {
        $actions = [
            new Action('admin.two_factor_auth.configuration.*', 'Configuration'),
            new Action('admin.two_factor_auth.user.reset', 'Reset User Settings'),
        ];

        $permissions = new RoleActionList;
        $permissions->add(Role::admin()->name, $actions);

        return $permissions;
    }

}
