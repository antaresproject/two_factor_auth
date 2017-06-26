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

namespace Antares\Modules\TwoFactorAuth\Tests\Processor;

use Antares\Model\User;
use Antares\Modules\TwoFactorAuth\Contracts\UserConfigurationListener;
use Antares\Modules\TwoFactorAuth\Model\UserConfig;
use Mockery as m;
use Antares\Testing\TestCase;
use Antares\Modules\TwoFactorAuth\Processor\UserConfigurationProcessor;
use Antares\Modules\TwoFactorAuth\Services\TwoFactorProvidersService;
use Antares\Modules\TwoFactorAuth\Contracts\ProvidersRepositoryContract;
use Antares\Modules\TwoFactorAuth\Http\Presenters\UserConfigurationPresenter;
use Antares\Modules\TwoFactorAuth\Services\UserProviderConfigService;
use Antares\Contracts\Html\Builder;
use Antares\Modules\TwoFactorAuth\Contracts\UserConfigRepositoryContract;
use Antares\Modules\TwoFactorAuth\Model\Provider;
use Antares\Area\Contracts\AreaContract;
use Illuminate\Contracts\View\View;
use Illuminate\Events\Dispatcher;
use Log;
use Exception;
use Antares\Area\AreaServiceProvider;

class UserConfigurationProcessorTest extends TestCase
{

    /**
     *
     * @var Mockery
     */
    protected $presenter;

    /**
     *
     * @var Mockery
     */
    protected $twoFactorProvidersService;

    /**
     *
     * @var Mockery
     */
    protected $userProviderConfigService;

    /**
     *
     * @var Mockery
     */
    protected $view;

    /**
     *
     * @var Mockery
     */
    protected $providersRepository;

    /**
     * @var Mockery
     */
    protected $dispatcher;

    /**
     * @var Mockery
     */
    protected $userConfigRepository;

    /**
     *
     * @var Mockery
     */
    protected $redirect;

    public function setUp()
    {
        $this->addProvider(AreaServiceProvider::class);

        parent::setUp();

        $this->dispatcher                = m::mock(Dispatcher::class);
        $this->presenter                 = m::mock(UserConfigurationPresenter::class);
        $this->twoFactorProvidersService = m::mock(TwoFactorProvidersService::class)->shouldReceive('bind')->once()->andReturnSelf()->getMock();
        $this->userProviderConfigService = m::mock(UserProviderConfigService::class);
        $this->userConfigRepository      = m::mock(UserConfigRepositoryContract::class);
        $this->view                      = m::mock(View::class)->makePartial();

        $this->providersRepository = m::mock(ProvidersRepositoryContract::class);
    }

    /**
     *
     * @return UserConfigurationProcessor
     */
    protected function getProcessor()
    {
        return new UserConfigurationProcessor($this->dispatcher, $this->presenter, $this->twoFactorProvidersService, $this->userProviderConfigService, $this->userConfigRepository);
    }

    public function testIsConfigured()
    {
        $area = m::mock(AreaContract::class);

        $this->userProviderConfigService
                ->shouldReceive('hasConfiguredArea')
                ->once()
                ->with($area)
                ->andReturnNull()
                ->getMock();

        $this->getProcessor()->isConfigured($area);
    }

    public function testConfigure()
    {
        $area        = m::mock(AreaContract::class);
        $returnValue = 'return value';
        $listener    = $this->getMockedConfigurationListener($returnValue, $area);

        $this->assertEquals($returnValue, $this->getProcessor()->configure($listener, $area));
    }

    public function testMarkAsConfigured()
    {
        $this->app->register(\Antares\Modules\TwoFactorAuth\TwoFactorAuthServiceProvider::class);
        $area = m::mock(AreaContract::class)
                        ->shouldReceive('getId')->once()->andReturn('administrators')->getMock();

        $userConfig = m::mock(UserConfig::class)->shouldReceive('getAttribute')->with('settings')->once()->andReturn(['secret_key' => 123])
                        ->shouldReceive('getId')->once()->andReturn('administrators')
                        ->shouldReceive('getAttribute')->once()->with('configured')->andReturn(1)->getMock();

        $this->userProviderConfigService
                ->shouldReceive('getSettingsByArea')->once()->with($area)->andReturn($userConfig)->getMock();

        $returnValue = 'return-value';

        $listener = m::mock(UserConfigurationListener::class)
                ->shouldReceive('afterConfiguration')
                ->once()
                ->with(m::type('Object'))
                ->andReturn($returnValue)
                ->getMock();

        //$this->app->instance(\Antares\Modules\TwoFactorAuth\Http\Presenters\AuthPresenter::class, m::mock(\Antares\Modules\TwoFactorAuth\Http\Presenters\AuthPresenter::class));
        $this->app[TwoFactorProvidersService::class] = $service                                     = m::mock(TwoFactorProvidersService::class);
        $service->shouldReceive('bind')->withNoArgs()->andReturnSelf()
                ->shouldReceive('getEnabledInArea')->with(m::type('Object'))->andReturn($provider                                    = m::mock(\Antares\Modules\TwoFactorAuth\Model\Provider::class));

        $provider->shouldReceive('getId')->withNoArgs()->andReturn('antares')
                ->shouldReceive('getProviderGateway')->withNoArgs()->andReturnSelf()
                ->shouldReceive('setupVerifyFormFieldset')->withAnyArgs()->andReturnSelf();

        $this->assertEquals($returnValue, $this->getProcessor()->markAsConfigured($listener, $area));
    }

    public function testEnableSuccessWithConfiguredArea()
    {
        $area = m::mock(AreaContract::class)
                ->shouldReceive('getLabel')
                ->once()
                ->andReturn(m::type('String'))
                ->getMock();

        $userConfig = m::mock('Eloquent', UserConfig::class)->shouldReceive('getAttribute')->with('id')->once()->andReturn(1)->getMock();

        $user = m::mock('Eloquent', User::class);

        $this->setupGetUserConfig($userConfig, $user, $area);

        $this->userProviderConfigService
                ->shouldReceive('hasConfiguredArea')
                ->with($area)
                ->once()
                ->andReturn(True)
                ->getMock();

        $this->userConfigRepository
                ->shouldReceive('markAsEnabledById')
                ->once()
                ->andReturnNull();

        $returnValue = 'return-value';

        $listener = m::mock(UserConfigurationListener::class)
                ->shouldReceive('afterConfiguration')
                ->with($area, m::type("String"))
                ->once()
                ->andReturn($returnValue)
                ->getMock();

        $this->assertEquals($returnValue, $this->getProcessor()->enable($listener, $user, $area));
    }

    public function testEnableSuccessWithNotConfiguredArea()
    {
        $area = m::mock(AreaContract::class);

        $userConfig = m::mock('Eloquent', UserConfig::class)
                ->shouldReceive('getAttribute')
                ->with('id')
                ->once()
                ->andReturn(1)
                ->getMock();

        $user = m::mock('Eloquent', User::class);

        $this->setupGetUserConfig($userConfig, $user, $area);

        $this->userProviderConfigService
                ->shouldReceive('hasConfiguredArea')
                ->with($area)
                ->once()
                ->andReturn(false)
                ->getMock();

        $this->userConfigRepository
                ->shouldReceive('markAsEnabledById')
                ->once()
                ->andReturnNull();

        $returnValue = 'return-value';
        $listener    = $this->getMockedConfigurationListener($returnValue, $area);

        $this->assertEquals($returnValue, $this->getProcessor()->enable($listener, $user, $area));
    }

    public function testEnableFailed()
    {
        $area = m::mock(AreaContract::class)
                ->shouldReceive('getLabel')
                ->once()
                ->andReturn(m::type('String'))
                ->getMock();

        $userConfig = m::mock('Eloquent', UserConfig::class)
                ->shouldReceive('getAttribute')
                ->with('id')
                ->once()
                ->andReturn(1)
                ->getMock();

        $user = m::mock('Eloquent', User::class);

        $this->setupGetUserConfig($userConfig, $user, $area);

        $exception = m::mock(Exception::class);

        $this->userConfigRepository
                ->shouldReceive('markAsEnabledById')
                ->once()
                ->andThrow($exception);

        $returnValue = 'return-value';

        $listener = m::mock(UserConfigurationListener::class)
                ->shouldReceive('enableFailed')
                ->with(m::type("String"))
                ->once()
                ->andReturn($returnValue)
                ->getMock();

        Log::shouldReceive('emergency')
                ->once()
                ->with($exception)
                ->andReturnNull();

        $this->assertEquals($returnValue, $this->getProcessor()->enable($listener, $user, $area));
    }

    public function testDisableSuccess()
    {
        $area = m::mock(AreaContract::class)
                ->shouldReceive('getLabel')
                ->once()
                ->andReturn(m::type('String'))
                ->getMock();

        $userConfig = m::mock('Eloquent', UserConfig::class)
                ->shouldReceive('getAttribute')
                ->with('id')
                ->once()
                ->andReturn(1)
                ->getMock();

        $user = m::mock('Eloquent', User::class);

        $this->setupGetUserConfig($userConfig, $user, $area);

        $this->userConfigRepository
                ->shouldReceive('markAsDisabledById')
                ->once()
                ->andReturnNull();

        $returnValue = 'return-value';

        $listener = m::mock(UserConfigurationListener::class)
                ->shouldReceive('disableSuccess')
                ->with(m::type("String"))
                ->once()
                ->andReturn($returnValue)
                ->getMock();

        $this->assertEquals($returnValue, $this->getProcessor()->disable($listener, $user, $area));
    }

    public function testDisableFailed()
    {
        $area = m::mock(AreaContract::class)
                ->shouldReceive('getLabel')
                ->once()
                ->andReturn(m::type('String'))
                ->getMock();

        $userConfig = m::mock('Eloquent', UserConfig::class)
                ->shouldReceive('getAttribute')
                ->with('id')
                ->once()
                ->andReturn(1)
                ->getMock();

        $user = m::mock('Eloquent', User::class);

        $this->setupGetUserConfig($userConfig, $user, $area);

        $exception = m::mock(Exception::class);

        $this->userConfigRepository
                ->shouldReceive('markAsDisabledById')
                ->once()
                ->andThrow($exception);

        $returnValue = 'return-value';

        $listener = m::mock(UserConfigurationListener::class)
                ->shouldReceive('disableFailed')
                ->with(m::type("String"))
                ->once()
                ->andReturn($returnValue)
                ->getMock();

        Log::shouldReceive('emergency')
                ->once()
                ->with($exception)
                ->andReturnNull();

        $this->assertEquals($returnValue, $this->getProcessor()->disable($listener, $user, $area));
    }

    protected function getMockedConfigurationListener($returnValue, $area)
    {
        $provider    = m::mock(Provider::class);
        $userConfig  = m::mock(UserConfig::class);
        $formBuilder = m::mock(Builder::class);

        $this->twoFactorProvidersService
                ->shouldReceive('getEnabledInArea')
                ->once()
                ->with($area)
                ->andReturn($provider)
                ->getMock();

        $this->userProviderConfigService
                ->shouldReceive('saveConfig')
                ->once()
                ->with($provider)
                ->andReturn($userConfig)
                ->getMock();

        $this->presenter
                ->shouldReceive('configure')
                ->once()
                ->with($userConfig, $area, $provider)
                ->andReturn($formBuilder)
                ->getMock();

        $this->dispatcher
                ->shouldReceive('fire')
                ->once()
                ->with('antares.form: two_factor_auth', [$provider, $formBuilder])
                ->andReturnNull()
                ->getMock();

        return m::mock(UserConfigurationListener::class)
                        ->shouldReceive('showConfiguration')
                        ->once()
                        ->with($provider, $formBuilder)
                        ->andReturn($returnValue)
                        ->getMock();
    }

    protected function setupGetUserConfig($userConfig, $user, $area)
    {
        $provider = m::mock(Provider::class)
                ->shouldReceive('getId')
                ->andReturn(1)
                ->getMock();

        $user
                ->shouldReceive('getAttribute')
                ->with('id')
                ->andReturn(1)
                ->getMock();

        $this->twoFactorProvidersService
                ->shouldReceive('getEnabledInArea')
                ->once()
                ->with($area)
                ->andReturn($provider)
                ->getMock();

        $this->userConfigRepository
                ->shouldReceive('findByUserIdAndProviderId')
                ->once()
                ->andReturn($userConfig)
                ->getMock();
    }

}
