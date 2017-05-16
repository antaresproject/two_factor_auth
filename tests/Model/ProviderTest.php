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

namespace Antares\Modules\TwoFactorAuth\Tests\Model;

use Mockery as m;
use Antares\Testing\TestCase;
use Antares\Modules\TwoFactorAuth\Model\Provider;
use Antares\Modules\TwoFactorAuth\Model\UserConfig;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Antares\Model\User;
use Antares\Modules\TwoFactorAuth\Contracts\ProviderGatewayContract;
use Antares\Area\AreaServiceProvider;

class ProviderTest extends TestCase
{

    /**
     *
     * @var Provider;
     */
    protected $model;

    public function setUp()
    {
        $this->addProvider(AreaServiceProvider::class);

        parent::setUp();

        $this->model          = new Provider;
        $this->model->id      = '1';
        $this->model->enabled = '1';
        $this->model->forced  = '0';
        $this->model->name    = 'google2fa';
        $this->model->area    = 'client';
    }

    public function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    public function testSetupAttributes()
    {
        $model = new Provider;

        $this->assertSame(false, $model->enabled);
    }

    public function testAttributes()
    {
        $this->assertSame(1, $this->model->id);
        $this->assertSame(true, $this->model->enabled);
        $this->assertSame(true, $this->model->isEnabled());
        $this->assertSame(false, $this->model->forced);
        $this->assertSame(false, $this->model->isForced());
    }

    public function testTableName()
    {
        $this->assertSame('tbl_two_factor_auth_providers', $this->model->getTable());
    }

    public function testIsEnabledMethod()
    {
        $this->assertSame(true, $this->model->isEnabled());
        $this->model->enabled = 0;
        $this->assertSame(false, $this->model->isEnabled());
    }

    public function testIsForcedMethod()
    {
        $this->assertSame(false, $this->model->isForced());
        $this->model->forced = 1;
        $this->assertSame(true, $this->model->isForced());
    }

    public function testUsersConfigRelation()
    {
        $relation = $this->model->usersConfig();

        $this->assertInstanceOf(HasMany::class, $relation);
        $this->assertInstanceOf(UserConfig::class, $relation->getRelated());
    }

    public function testUsersRelation()
    {
        $relation = $this->model->users();

        $this->assertInstanceOf(HasManyThrough::class, $relation);
        $this->assertInstanceOf(User::class, $relation->getRelated());
    }

    public function testGatewayMethod()
    {
        $this->assertNull($this->model->getProviderGateway());

        $gateway = m::mock(ProviderGatewayContract::class);
        $this->model->setProviderGateway($gateway);

        $this->assertInstanceOf(ProviderGatewayContract::class, $this->model->getProviderGateway());
    }

}
