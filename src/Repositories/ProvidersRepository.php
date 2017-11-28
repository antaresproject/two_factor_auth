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

namespace Antares\Modules\TwoFactorAuth\Repositories;

use Antares\Model\User;
use Antares\Modules\TwoFactorAuth\Model\Provider;
use Antares\Modules\TwoFactorAuth\Contracts\ProvidersRepositoryContract;
use Antares\Modules\TwoFactorAuth\Model\UserConfig;
use Exception;

class ProvidersRepository implements ProvidersRepositoryContract
{

    /**
     * Provider model.
     *
     * @var Provider
     */
    protected $provider;

    /**
     * ProvidersRepository constructor.
     * @param Provider $provider
     */
    public function __construct(Provider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->provider->newQuery()->get();
    }

    /**
     * {@inheritdoc}
     */
    public function findById($providerId)
    {
        return $this->provider->newQuery()->findOrFail($providerId);
    }

    /**
     * {@inheritdoc}
     */
    public function update(array $data)
    {
        $connection = $this->provider->getConnection();
        $connection->beginTransaction();

        try {
            foreach ($data as $areaId => $areaData) {
                if (array_get($areaData, 'enabled')) {
                    $providerId = array_get($areaData, 'id');

                    $this->provider->newQuery()->where('area', $areaId)->where('enabled', 1)->update(['enabled' => false]);
                    $this->provider->newQuery()->findOrFail($providerId)->fill($areaData)->save();
                } else {
                    $users = app(User::class)
                        ->whereHas('roles', function($query) use ($areaId) {
                            $query->where('tbl_roles.area', $areaId);
                        })
                        ->get()
                        ->pluck('id')
                        ->toArray();

                    if (!empty($users)) {
                        app(UserConfig::class)
                            //->where('provider_id', $providerId)
                            ->whereIn('user_id', $users)
                            ->delete();
                    }

                    $this->provider->newQuery()->where('area', $areaId)->update(['enabled' => false]);
                }
            }

            $connection->commit();
        } catch (Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

}
