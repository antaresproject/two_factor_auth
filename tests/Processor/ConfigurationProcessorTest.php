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






namespace Antares\TwoFactorAuth\Tests\Processor;

use Mockery as m;
use Antares\Testing\TestCase;
use Antares\TwoFactorAuth\Processor\ConfigurationProcessor;
use Antares\TwoFactorAuth\Http\Presenters\ConfigurationPresenter;
use Antares\TwoFactorAuth\Services\TwoFactorProvidersService;
use Antares\TwoFactorAuth\Contracts\ConfigurationListener;
use Antares\TwoFactorAuth\Contracts\ProvidersRepositoryContract;
use Antares\TwoFactorAuth\Contracts\ProviderGatewayContract;
use Antares\Contracts\Html\Builder;
use Antares\TwoFactorAuth\Model\Provider;
use Antares\Area\Contracts\AreaContract;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Exception;
use Antares\Area\AreaServiceProvider;

class ConfigurationProcessorTest extends TestCase {
    
    /**
     *
     * @var Mockery
     */
    protected $presenter;
    
    /**
     *
     * @var Mockery
     */
    protected $service;
    
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
     *
     * @var Mockery
     */
    protected $redirect;
    
    public function setUp() {
        $this->addProvider(AreaServiceProvider::class);

        parent::setUp();
        
        $this->presenter    = m::mock(ConfigurationPresenter::class);
        $this->service      = m::mock(TwoFactorProvidersService::class)->shouldReceive('bind')->once()->andReturnSelf()->getMock();
        $this->view         = m::mock(View::class)->makePartial();
        
        $this->providersRepository = m::mock(ProvidersRepositoryContract::class);
    }
    
    public function tearDown() {
        parent::tearDown();
        m::close();
    }
    
    /**
     * 
     * @return ConfigurationProcessor
     */
    protected function getProcessor() {
        return new ConfigurationProcessor($this->presenter, $this->service, $this->providersRepository);
    }
    
    public function testIndex() {
        $this->presenter->shouldReceive('index')->once()->andReturn($this->view);
        $this->service->shouldReceive('getAreaProvidersCollection')->once()->andReturn(new Collection);
        
        $processor = $this->getProcessor();
        
        $this->assertEquals($this->view, $processor->index());
    }
    
    public function testEdit() {
        $area = m::mock(AreaContract::class)
                ->shouldReceive('getId')
                ->andReturn('admin')
                ->getMock();
        
        $provider = m::mock('Eloquent', Provider::class)
                ->shouldReceive('setAttribute')
                ->twice()
                ->andReturnNull()
                ->shouldReceive('getAttribute')
                ->once()
                ->andReturn('string value')
                ->shouldReceive('setProviderGateway')
                ->once()
                ->andReturnNull()
                ->getMock();
        
        $form = m::mock(Builder::class);
        
        $this->service->shouldReceive('getProviderGatewayByName')
                ->with(m::type('String'))
                ->once()
                ->andReturn( m::mock(ProviderGatewayContract::class) )
                ->getMock();
        
        $this->presenter->shouldReceive('form')
                ->with($provider)
                ->once()
                ->andReturn($form);
        
        $listener = m::mock(ConfigurationListener::class)
                ->shouldReceive('showProviderConfiguration')
                ->with($area, $provider, $form)
                ->once()
                ->andReturn( m::mock(View::class) )
                ->getMock();
        
        $response = $this->getProcessor()->edit($listener, $area, $provider);
        
        $this->assertInstanceOf(View::class, $response);
    }
    
    public function testUpdateSuccess() {
        $input = ['id' => 1];
        
        $this->providersRepository->shouldReceive('update')
                ->with($input)
                ->once()
                ->andReturnNull();
        
        $listener = m::mock(ConfigurationListener::class)
                ->shouldReceive('updateSuccess')
                ->with(m::type("String"))
                ->once()
                ->andReturn( m::mock(RedirectResponse::class) )
                ->getMock();
        
        $response = $this->getProcessor()->update($listener, $input);
        
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }
    
    public function testUpdateFailed() {
        $input = ['id' => 1];
        
        $this->providersRepository->shouldReceive('update')
                ->with($input)
                ->once()
                ->andThrow( m::mock(Exception::class) );
        
        $listener = m::mock(ConfigurationListener::class)
                ->shouldReceive('updateFailed')
                ->with(m::type("String"))
                ->once()
                ->andReturn( m::mock(RedirectResponse::class) )
                ->getMock();
        
        $response = $this->getProcessor()->update($listener, $input);
        
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }
    
}