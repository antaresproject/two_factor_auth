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

namespace Antares\Modules\TwoFactorAuth\Tests\Processor;

use Antares\Model\User;
use Mockery as m;
use Antares\Testing\TestCase;
use Antares\Modules\TwoFactorAuth\Processor\UsersProcessor;
use Antares\Modules\TwoFactorAuth\Contracts\UsersListener;
use Antares\Modules\TwoFactorAuth\Contracts\UserConfigRepositoryContract;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Exception;
use Antares\Area\AreaServiceProvider;

class UsersProcessorTest extends TestCase
{

    /**
     *
     * @var Mockery
     */
    protected $userConfigService;

    /**
     *
     * @var Mockery
     */
    protected $view;

    /**
     *
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

        $this->view                 = m::mock(View::class)->makePartial();
        $this->userConfigRepository = m::mock(UserConfigRepositoryContract::class);
    }

    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    /**
     * 
     * @return UsersProcessor
     */
    protected function getProcessor()
    {
        return new UsersProcessor($this->userConfigRepository);
    }

    public function testResetUserConfigSuccess()
    {
        $userId = 1;

        $user = m::mock('Eloquent', User::class)
                ->shouldReceive('getAttribute')
                ->once()
                ->with('id')
                ->andReturn($userId)
                ->getMock();

        $this->userConfigRepository
                ->shouldReceive('deleteByUserId')
                ->with($userId)
                ->once()
                ->andReturnNull();

        $listener = m::mock(UsersListener::class)
                ->shouldReceive('resetSuccess')
                ->once()
                ->andReturn(m::mock(RedirectResponse::class))
                ->getMock();

        $response = $this->getProcessor()->resetUserConfig($listener, $user);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testResetUserConfigFailed()
    {
        $userId = 1;

        $user = m::mock('Eloquent', User::class)
                ->shouldReceive('getAttribute')
                ->once()
                ->with('id')
                ->andReturn($userId)
                ->getMock();

        $this->userConfigRepository
                ->shouldReceive('deleteByUserId')
                ->with($userId)
                ->once()
                ->andThrow(m::mock(Exception::class));

        $listener = m::mock(UsersListener::class)
                ->shouldReceive('resetFailed')
                ->once()
                ->andReturn(m::mock(RedirectResponse::class))
                ->getMock();

        $response = $this->getProcessor()->resetUserConfig($listener, $user);

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

}
