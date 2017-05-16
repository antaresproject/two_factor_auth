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

namespace Antares\Modules\TwoFactorAuth\Tests\Services;

use Antares\Area\AreaServiceProvider;
use Antares\Testing\TestCase;
use Antares\Modules\TwoFactorAuth\Services\AuthStore;
use Illuminate\Http\Request;
use Session;

class AuthStoreTest extends TestCase
{

    /**
     *
     * @var AuthStore
     */
    protected $authStore;

    public function setUp()
    {
        $this->addProvider(AreaServiceProvider::class);

        parent::setUp();

        Session::setDefaultDriver('array');

        $request = new Request();

        $request->setLaravelSession(app('session')->driver());

        $this->authStore = new AuthStore($request->getSession());
    }

    public function testIsNotVerified()
    {
        $this->assertFalse($this->authStore->isVerified());
    }

    public function testVerified()
    {
        $this->authStore->verify();
        $this->assertTrue($this->authStore->isVerified());
    }

    public function testUnverify()
    {
        $this->authStore->verify();
        $this->assertTrue($this->authStore->isVerified());

        $this->authStore->unverify();
        $this->assertFalse($this->authStore->isVerified());
    }

}
