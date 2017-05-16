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

namespace Antares\Modules\TwoFactorAuth\Http\Controllers\Admin\TestCase;

use Antares\Area\AreaServiceProvider;
use Antares\Modules\TwoFactorAuth\TwoFactorAuthServiceProvider;
use Antares\Modules\TwoFactorAuth\Processor\UsersProcessor;
use Antares\Modules\TwoFactorAuth\Contracts\UserConfigRepositoryContract;
use Antares\Testing\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Antares\Model\User;
use Mockery as m;

class UsersControllerTest extends TestCase
{

    use WithoutMiddleware;

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        $this->addProvider(AreaServiceProvider::class);
        $this->addProvider(TwoFactorAuthServiceProvider::class);

        parent::setUp();

        $this->disableMiddlewareForAllTests();
    }

    public function tearDown()
    {
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
        $userConfigRepository = m::mock(UserConfigRepositoryContract::class);
        $processor            = m::mock(UsersProcessor::class, [$userConfigRepository]);

        $this->app->instance(UsersProcessor::class, $processor);

        return $processor;
    }

    public function testGetResetWithSuccess()
    {
        $userId = User::first()->id;

        $this->getProcessorMock()
                ->shouldReceive('resetUserConfig')
                ->once()
                ->andReturnUsing(function ($listener) {
                    return $listener->resetSuccess();
                });

        $this->app['antares.messages'] = m::mock(\Antares\Contracts\Messages\MessageBag::class)
                ->shouldReceive('add')
                ->with('success', m::type('String'))
                ->once()
                ->andReturnNull()
                ->getMock();

        $this->call('GET', 'antares/two_factor_auth/user/' . $userId . '/reset');
        $this->assertResponseStatus(302);
    }

    public function testGetResetWithFailed()
    {
        $userId = User::first()->id;

        $this->getProcessorMock()
                ->shouldReceive('resetUserConfig')
                ->once()
                ->andReturnUsing(function ($listener) {
                    return $listener->resetFailed();
                });

        $this->app['antares.messages'] = m::mock(\Antares\Contracts\Messages\MessageBag::class)
                ->shouldReceive('add')
                ->with('error', m::type('String'))
                ->once()
                ->andReturnNull()
                ->getMock();

        $this->call('GET', 'antares/two_factor_auth/user/' . $userId . '/reset');
        $this->assertResponseStatus(302);
    }

}
