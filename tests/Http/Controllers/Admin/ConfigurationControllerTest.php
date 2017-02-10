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






namespace Antares\TwoFactorAuth\Http\Controllers\Admin\TestCase;

use Antares\Area\AreaServiceProvider;
use Antares\Contracts\Html\Form\Builder;
use Antares\TwoFactorAuth\Services\TwoFactorProvidersService;
use Antares\TwoFactorAuth\TwoFactorAuthServiceProvider;
use Antares\TwoFactorAuth\Contracts\ConfigurationPresenter;
use Antares\TwoFactorAuth\Processor\ConfigurationProcessor;
use Antares\TwoFactorAuth\Contracts\ProvidersRepositoryContract;
use Antares\TwoFactorAuth\Model\Provider;
use Antares\TwoFactorAuth\Contracts\ProviderGatewayContract;
use Antares\Testing\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Mockery as m;

class ConfigurationControllerTest extends TestCase
{

    use WithoutMiddleware;
    
    /**
     *
     * @var Mockery
     */
    protected $presenter;
    
    /**
     *
     * @var Mockery
     */
    protected $repository;
    
    /**
     *
     * @var Mockery
     */
    protected $service;

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        $this->addProvider(AreaServiceProvider::class);
        $this->addProvider(TwoFactorAuthServiceProvider::class);
        
        parent::setUp();
        
        $this->disableMiddlewareForAllTests();
        
        $this->presenter    = m::mock(ConfigurationPresenter::class);
        $this->repository   = m::mock(ProvidersRepositoryContract::class);
        $this->service      = m::mock(TwoFactorProvidersService::class)->shouldReceive('bind')->once()->andReturnSelf()->getMock();
        
        $this->app->instance(ProvidersRepositoryContract::class, $this->repository);
    }
    
    public function tearDown() {
        parent::tearDown();
        m::close();
    }

    /**
     * Get processor mock.
     *
     * @return Mockery
     */
    protected function getProcessorMock()
    {
        $processor = m::mock(ConfigurationProcessor::class, [$this->presenter, $this->service, $this->repository]);

        $this->app->instance(ConfigurationProcessor::class, $processor);

        return $processor;
    }
    
    public function testIndex()
    {
        $this->getProcessorMock()->shouldReceive('index')->once()->andReturn(View::class);
        $this->call('GET', 'admin/two_factor_auth');
        $this->assertResponseOk();
    }
    
    public function testEditNotAjax()
    {
        $provider = m::mock(Provider::class);

        $this->repository
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($provider)
            ->getMock();

        $this->getProcessorMock()
            ->shouldReceive('edit')
            ->once()
            ->andReturnUsing(function ($listener, $area, $provider) {
                return $listener->showProviderConfiguration($area, $provider, m::mock(Builder::class));
            });

        $this->call('GET', 'admin/two_factor_auth/configuration/area/admin/provider/1/edit');
        $this->assertResponseStatus(405);
    }

    public function testEditWithAjax()
    {
        $provider = m::mock(Provider::class);
        
        $builder = m::mock(Builder::class)
            ->shouldReceive('render')
            ->once()
            ->andReturn(m::type('String'))
            ->getMock();

        $this->repository
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($provider)
            ->getMock();

        $this->getProcessorMock()
            ->shouldReceive('edit')
            ->once()
            ->andReturnUsing(function ($listener, $area, $provider) use($builder) {
                return $listener->showProviderConfiguration($area, $provider, $builder);
            });

        $request = m::mock(Request::class)
            ->shouldReceive('ajax')
            ->once()
            ->andReturn(true)
            ->getMock();

        $this->app->instance(Request::class, $request);

        $this->call('GET', 'admin/two_factor_auth/configuration/area/admin/provider/1/edit');
        $this->assertResponseOk();
    }
    
    public function testUpdateFailed()
    {
        $this->getProcessorMock()
                ->shouldReceive('update')
                ->once()
                ->andReturnUsing(function ($listener, $msg) {
                    return $listener->updateFailed($msg);
                });
        
        $this->call('POST', 'admin/two_factor_auth/configuration/update');
        $this->assertResponseStatus(302);
    }
    
    public function testUpdateSuccess()
    {
        $this->getProcessorMock()
                ->shouldReceive('update')
                ->once()
                ->andReturnUsing(function ($listener, $msg) {
                    return $listener->updateSuccess($msg);
                });
        
        $this->call('POST', 'admin/two_factor_auth/configuration/update');
        $this->assertResponseStatus(302);
    }
    
}
