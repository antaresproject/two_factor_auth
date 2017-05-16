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

use Antares\Testing\TestCase;
use Antares\Modules\TwoFactorAuth\Model\UserConfig;
use Antares\Modules\TwoFactorAuth\Model\Provider;
use Antares\Model\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Antares\Area\AreaServiceProvider;

class UserConfigTest extends TestCase
{

    /**
     *
     * @var UserConfig;
     */
    protected $model;

    public function setUp()
    {
        $this->addProvider(AreaServiceProvider::class);

        parent::setUp();

        $this->model              = new UserConfig;
        $this->model->id          = '1';
        $this->model->provider_id = '1';
        $this->model->user_id     = '1';
        $this->model->settings    = ['text' => 'foo'];
        $this->model->configured  = '0';
    }

    public function testSetupAttributes()
    {
        $model = new UserConfig;

        $this->assertSame(false, $model->configured);
    }

    public function testAttributes()
    {
        $this->assertSame(1, $this->model->id);
        $this->assertSame(1, $this->model->provider_id);
        $this->assertSame(1, $this->model->user_id);
        $this->assertSame(false, $this->model->configured);
        $this->assertInternalType('array', $this->model->settings);
    }

    public function testTableName()
    {
        $this->assertSame('tbl_two_factor_auth_users', $this->model->getTable());
    }

    public function testIsConfiguredMethod()
    {
        $this->assertSame(false, $this->model->isConfigured());
        $this->model->configured = 1;
        $this->assertSame(true, $this->model->isConfigured());
    }

    public function testProviderRelation()
    {
        $relation = $this->model->provider();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(Provider::class, $relation->getRelated());
    }

    public function testUserRelation()
    {
        $relation = $this->model->user();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertInstanceOf(User::class, $relation->getRelated());
    }

}
