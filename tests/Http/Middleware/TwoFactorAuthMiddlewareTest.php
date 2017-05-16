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

namespace Antares\Modules\TwoFactorAuth\Tests\Http\Middleware;

use Antares\Modules\TwoFactorAuth\Http\Middleware\TwoFactorAuthMiddleware;
use Mockery as m;
use Antares\Testing\TestCase;
use Antares\Modules\TwoFactorAuth\Services\VerificationService;
use Antares\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Route;
use Antares\Area\AreaServiceProvider;

class TwoFactorAuthMiddlewareTest extends TestCase
{

    /**
     * @var Mockery
     */
    protected $verificationService;

    /**
     * @var Mockery
     */
    protected $guard;

    public function setUp()
    {
        $this->addProvider(AreaServiceProvider::class);

        parent::setUp();

        $this->verificationService = m::mock(VerificationService::class);
        $this->guard               = m::mock(Guard::class);
    }

    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    /**
     * @return TwoFactorAuthMiddleware
     */
    protected function getMiddleware()
    {
        return new TwoFactorAuthMiddleware($this->verificationService, $this->guard);
    }

    public function testVerificationAsNotNeeded()
    {
        $route = m::mock(Route::class);

        $this->verificationService
                ->shouldReceive('mustBeVerified')
                ->once()
                ->with($route, $this->guard)
                ->andReturn(false)
                ->getMock();

        $request = m::mock(Request::class)
                ->shouldReceive('route')
                ->once()
                ->andReturn($route)
                ->getMock();

        $next = function() {
            return 'next request';
        };

        $this->assertEquals('next request', $this->getMiddleware()->handle($request, $next));
    }

    public function testVerificationAsNeededAndAjax()
    {
        $route = m::mock(Route::class);

        $this->verificationService
                ->shouldReceive('mustBeVerified')
                ->once()
                ->with($route, $this->guard)
                ->andReturn(true)
                ->getMock();

        $request = m::mock(Request::class)
                ->shouldReceive('route')
                ->once()
                ->andReturn($route)
                ->shouldReceive('ajax')
                ->once()
                ->andReturn(true)
                ->getMock();

        $next = function() {
            return 'next request';
        };

        $response = $this->getMiddleware()->handle($request, $next);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testVerificationAsNeeded()
    {
        $route        = m::mock(Route::class);
        $redirectPath = 'redirect-path';

        $this->verificationService
                ->shouldReceive('mustBeVerified')
                ->once()
                ->with($route, $this->guard)
                ->andReturn(true)
                ->shouldReceive('getPathToVerificationAction')
                ->once()
                ->andReturn($redirectPath)
                ->getMock();

        $request = m::mock(Request::class)
                ->shouldReceive('route')
                ->once()
                ->andReturn($route)
                ->shouldReceive('ajax')
                ->once()
                ->andReturn(false)
                ->getMock();

        $next = function() {
            return 'next request';
        };

        $response = $this->getMiddleware()->handle($request, $next);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertContains($redirectPath, $response->getTargetUrl());
    }

}
