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

namespace Antares\Modules\TwoFactorAuth\Tests\Services;

use Antares\Area\Contracts\AreaManagerContract;
use Antares\Area\Model\Area;
use Antares\Model\User;
use Antares\Testing\TestCase;
use Antares\Modules\TwoFactorAuth\Contracts\UserConfigRepositoryContract;
use Antares\Modules\TwoFactorAuth\Services\AuthStore;
use Antares\Modules\TwoFactorAuth\Services\TwoFactorProvidersService;
use Antares\Modules\TwoFactorAuth\Services\UserProviderConfigService;
use Antares\Modules\TwoFactorAuth\Services\VerificationService;
use Antares\Contracts\Auth\Guard;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Routing\Route;
use Antares\Area\AreaServiceProvider;
use Mockery as m;
use ReflectionClass;

class VerificationServiceTest extends TestCase
{

    /**
     * @var Mockery
     */
    protected $areaManager;

    /**
     * @var Mockery
     */
    protected $twoFactorProvidersService;

    /**
     * @var Mockery
     */
    protected $userConfigRepository;

    /**
     * @var Mockery
     */
    protected $userProviderConfigService;

    /**
     * @var Mo0ckery
     */
    protected $urlGenerator;

    public function setUp()
    {
        $this->addProvider(AreaServiceProvider::class);


        parent::setUp();

        $this->areaManager               = m::mock(AreaManagerContract::class);
        $this->twoFactorProvidersService = m::mock(TwoFactorProvidersService::class);
        $this->userConfigRepository      = m::mock(UserConfigRepositoryContract::class);
        $this->userProviderConfigService = m::mock(UserProviderConfigService::class);
        $this->urlGenerator              = m::mock(UrlGenerator::class);
    }

    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    /**
     * @return VerificationService
     */
    protected function getVerificationService()
    {
        return new VerificationService($this->areaManager, $this->twoFactorProvidersService, $this->userConfigRepository, $this->userProviderConfigService, $this->urlGenerator);
    }

    public function testGetPathToVerificationAction()
    {

        $currentArea = new Area('administrators', 'Admin Area');

        $this->areaManager
                ->shouldReceive('getCurrentArea')
                ->once()
                ->andReturn($currentArea)
                ->getMock();




        $route = $this->getVerificationService()->getPathToVerificationAction();
        $this->assertSame("", $route);
    }

    public function testRouteBelongsToComponentWithValidName()
    {
        $reflectionClass  = new ReflectionClass(VerificationService::class);
        $reflectionMethod = $reflectionClass->getMethod('routeBelongsToComponent');
        $reflectionMethod->setAccessible(true);

        $route = m::mock(Route::class)
                ->shouldReceive('getName')
                ->once()
                ->andReturn('two_factor_auth.some')
                ->getMock();

        $results = $reflectionMethod->invoke($this->getVerificationService(), $route);

        $this->assertTrue($results);
    }

    public function testRouteBelongsToComponentWithInvalidName()
    {
        $reflectionClass  = new ReflectionClass(VerificationService::class);
        $reflectionMethod = $reflectionClass->getMethod('routeBelongsToComponent');
        $reflectionMethod->setAccessible(true);

        $route = m::mock(Route::class)
                ->shouldReceive('getName')
                ->once()
                ->andReturn('some.route.name')
                ->getMock();

        $results = $reflectionMethod->invoke($this->getVerificationService(), $route);

        $this->assertFalse($results);
    }

    public function testMustBeVerifiedAsGuest()
    {
        $route = m::mock(Route::class);

        $guard = m::mock(Guard::class)
                ->shouldReceive('guest')
                ->once()
                ->andReturn(true)
                ->getMock();

        $authStore = m::mock(AuthStore::class)
                ->shouldReceive('unverify')
                ->once()
                ->andReturnNull()
                ->getMock();

        $this->twoFactorProvidersService
                ->shouldReceive('getAuthStore')
                ->once()
                ->andReturn($authStore)
                ->getMock();

        $this->assertFalse($this->getVerificationService()->mustBeVerified($route, $guard));
    }

    public function testMustBeVerifiedInComponentRoute()
    {
        $route = m::mock(Route::class)
                ->shouldReceive('getName')
                ->once()
                ->andReturn('two_factor_auth')
                ->getMock();

        $guard = m::mock(Guard::class)
                ->shouldReceive('guest')
                ->once()
                ->andReturn(false)
                ->getMock();

        $this->assertFalse($this->getVerificationService()->mustBeVerified($route, $guard));
    }

    public function testMustBeVerifiedAsAlreadyVerified()
    {
        $route = m::mock(Route::class)
                ->shouldReceive('getName')
                ->once()
                ->andReturn('some.route.name')
                ->getMock();

        $guard = m::mock(Guard::class)
                ->shouldReceive('guest')
                ->once()
                ->andReturn(false)
                ->getMock();

        $authStore = m::mock(AuthStore::class)
                ->shouldReceive('isVerified')
                ->once()
                ->andReturn(true)
                ->getMock();

        $this->twoFactorProvidersService
                ->shouldReceive('getAuthStore')
                ->once()
                ->andReturn($authStore)
                ->getMock();

        $this->assertFalse($this->getVerificationService()->mustBeVerified($route, $guard));
    }

    public function testMustBeVerifiedInRequiredArea()
    {
        $currentArea = new Area('admin', 'Admin Area');

        $this->areaManager
                ->shouldReceive('getCurrentArea')
                ->once()
                ->andReturn($currentArea)
                ->getMock();

        $route = m::mock(Route::class)
                ->shouldReceive('getName')
                ->once()
                ->andReturn('some.route.name')
                ->getMock();

        $guard = m::mock(Guard::class)
                ->shouldReceive('guest')
                ->once()
                ->andReturn(false)
                ->getMock();

        $authStore = m::mock(AuthStore::class)
                ->shouldReceive('isVerified')
                ->once()
                ->andReturn(false)
                ->getMock();

        $this->twoFactorProvidersService
                ->shouldReceive('getAuthStore')
                ->once()
                ->andReturn($authStore)
                ->shouldReceive('bind')
                ->once()
                ->andReturnSelf()
                ->shouldReceive('isRequiredInArea')
                ->once()
                ->with($currentArea)
                ->andReturn(true)
                ->getMock();

        $this->assertTrue($this->getVerificationService()->mustBeVerified($route, $guard));
    }

    public function testMustBeVerifiedInEnabledAreaForUser()
    {
        $currentArea = new Area('admin', 'Admin Area');

        $this->areaManager
                ->shouldReceive('getCurrentArea')
                ->once()
                ->andReturn($currentArea)
                ->getMock();

        $user = m::mock(User::class);

        $route = m::mock(Route::class)
                ->shouldReceive('getName')
                ->once()
                ->andReturn('some.route.name')
                ->getMock();

        $guard = m::mock(Guard::class)
                ->shouldReceive('guest')
                ->once()
                ->andReturn(false)
                ->shouldReceive('user')
                ->once()
                ->andReturn($user)
                ->getMock();

        $authStore = m::mock(AuthStore::class)
                ->shouldReceive('isVerified')
                ->once()
                ->andReturn(false)
                ->getMock();

        $this->twoFactorProvidersService
                ->shouldReceive('getAuthStore')
                ->once()
                ->andReturn($authStore)
                ->shouldReceive('bind')
                ->once()
                ->andReturnSelf()
                ->shouldReceive('isRequiredInArea')
                ->once()
                ->with($currentArea)
                ->andReturn(false)
                ->getMock();

        $this->userProviderConfigService
                ->shouldReceive('setUser')
                ->once()
                ->with($user)
                ->andReturnSelf()
                ->shouldReceive('hasEnabledArea')
                ->with($currentArea)
                ->once()
                ->andReturn(true)
                ->getMock();

        $this->assertTrue($this->getVerificationService()->mustBeVerified($route, $guard));
    }

    public function testMustBeVerifiedInNotEnabledAreaForUser()
    {
        $currentArea = new Area('admin', 'Admin Area');

        $this->areaManager
                ->shouldReceive('getCurrentArea')
                ->once()
                ->andReturn($currentArea)
                ->getMock();

        $user = m::mock(User::class);

        $route = m::mock(Route::class)
                ->shouldReceive('getName')
                ->once()
                ->andReturn('some.route.name')
                ->getMock();

        $guard = m::mock(Guard::class)
                ->shouldReceive('guest')
                ->once()
                ->andReturn(false)
                ->shouldReceive('user')
                ->once()
                ->andReturn($user)
                ->getMock();

        $authStore = m::mock(AuthStore::class)
                ->shouldReceive('isVerified')
                ->once()
                ->andReturn(false)
                ->getMock();

        $this->twoFactorProvidersService
                ->shouldReceive('getAuthStore')
                ->once()
                ->andReturn($authStore)
                ->shouldReceive('bind')
                ->once()
                ->andReturnSelf()
                ->shouldReceive('isRequiredInArea')
                ->once()
                ->with($currentArea)
                ->andReturn(false)
                ->getMock();

        $this->userProviderConfigService
                ->shouldReceive('setUser')
                ->once()
                ->with($user)
                ->andReturnSelf()
                ->shouldReceive('hasEnabledArea')
                ->with($currentArea)
                ->once()
                ->andReturn(false)
                ->getMock();

        $this->assertFalse($this->getVerificationService()->mustBeVerified($route, $guard));
    }

}
