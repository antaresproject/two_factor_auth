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

namespace Antares\Modules\TwoFactorAuth\Tests\Facades;

use Antares\Testing\TestCase;
use Antares\Area\Model\Area;
use Antares\Modules\TwoFactorAuth\Facades\AreaProviders;
use Antares\Modules\TwoFactorAuth\Model\Provider;
use Antares\Area\AreaServiceProvider;

class AreaProvidersTest extends TestCase
{

    /**
     *
     * @var Area
     */
    protected $area;

    /**
     *
     * @var AreaProviders
     */
    protected $areaProviders;

    public function setUp()
    {
        $this->addProvider(AreaServiceProvider::class);

        parent::setUp();

        $this->area          = new Area('client', 'Client Area');
        $this->areaProviders = new AreaProviders($this->area);
    }

    public function testInitial()
    {
        $this->assertSame($this->area, $this->areaProviders->getArea());
        $this->assertCount(0, $this->areaProviders->getModels());
        $this->assertNull($this->areaProviders->getEnabledModel());
    }

    public function testAddedModels()
    {
        $this->populateByModels();

        $this->assertCount(10, $this->areaProviders->getModels());
    }

    public function testEnabledModel()
    {
        $this->populateByModels();

        $model = $this->areaProviders->getEnabledModel();

        $this->assertInstanceOf(Provider::class, $model);
        $this->assertEquals(5, $model->id);
    }

    protected function populateByModels()
    {
        for ($i = 0; $i < 10; ++$i) {
            $model          = new Provider;
            $model->id      = $i;
            $model->enabled = ($i === 5);
            $model->name    = 'provider-id-' . $i;
            $model->area    = 'client';

            $this->areaProviders->addModel($model);
        }
    }

}
