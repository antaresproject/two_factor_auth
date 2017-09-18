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

namespace Antares\Modules\TwoFactorAuth\Tests\Repositories;

use Mockery as m;
use Antares\Modules\TwoFactorAuth\Model\UserConfig;
use Antares\Modules\TwoFactorAuth\Repositories\UserConfigRepository;
use Illuminate\Database\Eloquent\Builder;
use Antares\Testing\TestCase;
use Antares\Area\AreaServiceProvider;

class UserConfigRepositoryTest extends TestCase
{

    /**
     *
     * @var Mockery
     */
    protected $userConfig;

    /**
     *
     * @var Mockery
     */
    protected $builder;

    /**
     *
     * @var UserConfigRepository
     */
    protected $repository;

    public function setUp()
    {
        $this->addProvider(AreaServiceProvider::class);

        parent::setUp();

        $this->builder    = m::mock(Builder::class);
        $this->userConfig = m::mock('Eloquent', UserConfig::class)
                ->shouldReceive('newQuery')
                ->atLeast(1)
                ->andReturn($this->builder);

        $this->repository = new UserConfigRepository($this->userConfig->getMock());
    }

    public function testSaveWithUpdate()
    {
        $providerId = 1;
        $userId     = 1;
        $data       = [
            'provider_id' => $providerId,
            'user_id'     => $userId,
        ];

        $userConfig = m::mock(UserConfig::class)
                ->shouldReceive('update')
                ->with($data)
                ->once()
                ->andReturn(1)
                ->getMock();

        $this->builder
                ->shouldReceive('where')
                ->with('provider_id', $providerId)
                ->once()
                ->andReturnSelf()
                ->shouldReceive('where')
                ->with('user_id', $userId)
                ->once()
                ->andReturnSelf()
                ->shouldReceive('first')
                ->once()
                ->andReturn($userConfig);

        $results = $this->repository->save($data);

        $this->assertInstanceOf(UserConfig::class, $results);
    }

    public function testSaveWithCreate()
    {
        $providerId = 1;
        $userId     = 1;
        $data       = [
            'provider_id' => $providerId,
            'user_id'     => $userId,
        ];

        $this->userConfig->shouldReceive('create')
                ->with($data)
                ->once()
                ->andReturnSelf();

        $this->builder
                ->shouldReceive('where')
                ->with('provider_id', $providerId)
                ->once()
                ->andReturnSelf()
                ->shouldReceive('where')
                ->with('user_id', $userId)
                ->once()
                ->andReturnSelf()
                ->shouldReceive('first')
                ->once()
                ->andReturnNull();

        $results = $this->repository->save($data);

        $this->assertInstanceOf(UserConfig::class, $results);
    }

    public function testDeleteByUserId()
    {
        $userId = 1;

        $userConfig = m::mock(UserConfig::class)
                ->shouldReceive('delete')
                ->once()
                ->andReturn(1)
                ->getMock();

        $this->builder
                ->shouldReceive('where')
                ->with('user_id', $userId)
                ->once()
                ->andReturn($userConfig);

        $results = $this->repository->deleteByUserId($userId);

        $this->assertNull($results);
    }

    public function testFindByUserIdAndProviderId()
    {
        $userId     = 1;
        $providerId = 1;

        $userConfig = m::mock(UserConfig::class);

        $this->builder
                ->shouldReceive('where')
                ->with('user_id', $userId)
                ->once()
                ->andReturnSelf()
                ->shouldReceive('where')
                ->with('provider_id', $providerId)
                ->once()
                ->andReturnSelf()
                ->shouldReceive('first')
                ->once()
                ->andReturn($userConfig);

        $results = $this->repository->findByUserIdAndProviderId($userId, $providerId);

        $this->assertInstanceOf(UserConfig::class, $results);
    }

    public function testFindByUserId()
    {
        $userId     = 1;
        $userConfig = m::mock(UserConfig::class);

        $this->builder
                ->shouldReceive('where')
                ->with('user_id', $userId)
                ->once()
                ->andReturnSelf()
                ->shouldReceive('first')
                ->once()
                ->andReturn($userConfig);

        $results = $this->repository->findByUserId($userId);

        $this->assertInstanceOf(UserConfig::class, $results);
    }

    public function testMarkAsConfiguredById()
    {
        $id = 1;

        $userConfig = m::mock(UserConfig::class)
                ->shouldReceive('update')
                ->with(['configured' => true])
                ->once()
                ->andReturn(1)
                ->getMock();

        $this->builder
                ->shouldReceive('where')
                ->with('id', $id)
                ->once()
                ->andReturn($userConfig);

        $results = $this->repository->markAsConfiguredById($id);

        $this->assertNull($results);
    }

    public function testMarkAsEnabledById()
    {
        $id = 1;

        $userConfig = m::mock(UserConfig::class)
                ->shouldReceive('update')
                ->with(['enabled' => true])
                ->once()
                ->andReturn(1)
                ->getMock();

        $this->builder
                ->shouldReceive('where')
                ->with('id', $id)
                ->once()
                ->andReturn($userConfig);

        $results = $this->repository->markAsEnabledById($id);

        $this->assertNull($results);
    }

    public function testMarkAsDisabledById()
    {
        $id = 1;

        $userConfig = m::mock(UserConfig::class)
                ->shouldReceive('update')
                ->with(['enabled' => false])
                ->once()
                ->andReturn(1)
                ->getMock();

        $this->builder
                ->shouldReceive('where')
                ->with('id', $id)
                ->once()
                ->andReturn($userConfig);

        $results = $this->repository->markAsDisabledById($id);

        $this->assertNull($results);
    }

}
