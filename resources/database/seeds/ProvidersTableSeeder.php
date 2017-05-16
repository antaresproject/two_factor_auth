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
use Illuminate\Database\Seeder;
use Antares\Modules\TwoFactorAuth\Model\Provider;
use Antares\Modules\TwoFactorAuth\TwoFactorAuthServiceProvider;
use Antares\Modules\TwoFactorAuth\Services\TwoFactorProvidersService;
use Antares\Modules\TwoFactorAuth\Contracts\ProviderGatewayContract;
use Carbon\Carbon;

class ProvidersTableSeeder extends Seeder
{

    /**
     *
     * @var string
     */
    protected $providerTableName;

    /**
     *
     * @var TwoFactorProvidersService 
     */
    protected $service;

    /**
     *
     * @var ProviderGatewayContract[]
     */
    protected $registeredProviders = [];

    public function __construct()
    {
        app()->register(TwoFactorAuthServiceProvider::class);

        $this->providerTableName = with(new Provider)->getTable();
        $this->service           = app()->make(TwoFactorProvidersService::class);

        $providers = config('antares/two_factor_auth::providers', []);

        foreach ($providers as $item) {
            if (isset($item['provider'])) {
                $this->registeredProviders[] = app()->make($item['provider']);
            }
        }
    }

    public function run()
    {
        $this->down();
        $areas       = $this->service->getAreaManager()->getAreas();
        $currentDate = Carbon::now()->toDateTimeString();
        $inserts     = [];

        foreach ($areas as $area) {
            foreach ($this->registeredProviders as $provider) {
                $inserts[] = [
                    'area'       => $area->getId(),
                    'name'       => $provider->getName(),
                    'created_at' => $currentDate,
                    'updated_at' => $currentDate,
                ];
            }
        }

        if (count($inserts)) {
            DB::transaction(function() use($inserts) {
                DB::table($this->providerTableName)->insert($inserts);
            });
        }
    }

    public function down()
    {
        DB::transaction(function() {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table($this->providerTableName)->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        });
    }

}
