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

namespace Antares\Modules\TwoFactorAuth\Tests\Repositories;

use Mockery as m;
use Antares\Modules\TwoFactorAuth\Model\Provider;
use Antares\Modules\TwoFactorAuth\Repositories\ProvidersRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Antares\Testing\TestCase;
use Antares\Area\AreaServiceProvider;

class ProvidersRepositoryTest extends TestCase
{

    /**
     *
     * @var Mockery
     */
    protected $provider;

    /**
     *
     * @var Mockery
     */
    protected $builder;

    /**
     *
     * @var ProvidersRepository
     */
    protected $repository;

    public function setUp()
    {
        $this->addProvider(AreaServiceProvider::class);

        parent::setUp();

        $this->builder  = m::mock(Builder::class);
        $this->provider = m::mock('Eloquent', Provider::class)
                ->shouldReceive('newQuery')
                ->atLeast(1)
                ->andReturn($this->builder);

        $this->repository = new ProvidersRepository($this->provider->getMock());
    }

    public function tearDown()
    {
        m::close();
    }

    public function testAll()
    {
        $this->builder
                ->shouldReceive('get')
                ->once()
                ->andReturn(m::mock(Collection::class));

        $results = $this->repository->all();

        $this->assertInstanceOf(Collection::class, $results);
    }

    public function testFindById()
    {
        $providerId = 1;

        $this->builder
                ->shouldReceive('findOrFail')
                ->with($providerId)
                ->once()
                ->andReturn(m::mock(Provider::class));

        $provider = $this->repository->findById($providerId);

        $this->assertInstanceOf(Provider::class, $provider);
    }

    public function testSuccessUpdateAsDisabled()
    {
;
        $data = [
            'admin'  => [
                'enabled' => false,
                'id'      => 1
            ],
            'client' => [
                'id' => 1
            ],
        ];

        $connection = m::mock(\Illuminate\Database\Connection::class)
                ->shouldReceive('beginTransaction')
                ->once()
                ->andReturnNull()
                ->shouldReceive('commit')
                ->once()
                ->andReturnNull();

        $this->provider->shouldReceive('getConnection')
                ->once()
                ->andReturn($connection->getMock());

        $this->builder
                ->shouldReceive('where')
                ->with('area', 'admin')
                ->once()
                ->andReturnSelf()
                ->shouldReceive('where')
                ->with('area', 'client')
                ->once()
                ->andReturnSelf()
                ->shouldReceive('update')
                ->with(['enabled' => false])
                ->twice()
                ->andReturn(1);

        $results = $this->repository->update($data);

        $this->assertNull($results);
    }

    public function testSuccessUpdateAsEnabled()
    {
        $data = [
            'admin'  => [
                'enabled' => true,
                'id'      => 1
            ],
            'client' => [
                'enabled' => true,
                'id'      => 1
            ],
        ];

        $connection = m::mock(\Illuminate\Database\Connection::class)
                ->shouldReceive('beginTransaction')
                ->once()
                ->andReturnNull()
                ->shouldReceive('commit')
                ->once()
                ->andReturnNull();

        $this->provider->shouldReceive('getConnection')
                ->once()
                ->andReturn($connection->getMock());

        $provider = m::mock('Eloquent', Provider::class)
                ->shouldReceive('fill')
                ->with($data['admin'])
                ->once()
                ->andReturnSelf()
                ->shouldReceive('save')
                ->once()
                ->andReturn(1)
                ->shouldReceive('fill')
                ->with($data['client'])
                ->once()
                ->andReturnSelf()
                ->shouldReceive('save')
                ->once()
                ->andReturn(1)
                ->getMock();

        $this->builder
                ->shouldReceive('where')
                ->with('area', 'admin')
                ->once()
                ->andReturnSelf()
                ->shouldReceive('where')
                ->with('area', 'client')
                ->once()
                ->andReturnSelf()
                ->shouldReceive('where')
                ->with('enabled', 1)
                ->twice()
                ->andReturnSelf()
                ->shouldReceive('update')
                ->with(['enabled' => false])
                ->twice()
                ->andReturn(1)
                ->shouldReceive('findOrFail')
                ->with(1)
                ->twice()
                ->andReturn($provider);

        $results = $this->repository->update($data);

        $this->assertNull($results);
    }

}
