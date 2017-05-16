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

namespace Antares\Modules\TwoFactorAuth\Repositories;

use Antares\Modules\TwoFactorAuth\Model\UserConfig;
use Antares\Modules\TwoFactorAuth\Contracts\UserConfigRepositoryContract;

class UserConfigRepository implements UserConfigRepositoryContract
{

    /**
     * User config model.
     *
     * @var UserConfig
     */
    protected $userConfig;

    /**
     * UserConfigRepository constructor.
     * @param UserConfig $userConfig
     */
    public function __construct(UserConfig $userConfig)
    {
        $this->userConfig = $userConfig;
    }

    /**
     * 
     * {@inheritdoc}
     */
    public function save(array $data)
    {
        $providerId = array_get($data, 'provider_id');
        $userId     = array_get($data, 'user_id');

        $model = $this->userConfig->newQuery()->where('provider_id', $providerId)->where('user_id', $userId)->first();

        if ($model) {
            $model->update($data);
            return $model;
        }

        return $this->userConfig->create($data);
    }

    /**
     * 
     * {@inheritdoc}
     */
    public function deleteByUserId($userId)
    {
        $this->userConfig->newQuery()->where('user_id', $userId)->delete();
    }

    /**
     * 
     * {@inheritdoc}
     */
    public function findByUserIdAndProviderId($userId, $providerId)
    {
        return $this->userConfig->newQuery()->where('user_id', $userId)->where('provider_id', $providerId)->first();
    }

    /**
     * 
     * {@inheritdoc}
     */
    public function findByUserId($userId)
    {
        return $this->userConfig->newQuery()->where('user_id', $userId)->first();
    }

    /**
     * 
     * {@inheritdoc}
     */
    public function markAsConfiguredById($id)
    {
        $this->userConfig->newQuery()->where('id', $id)->update(['configured' => true]);
    }

    /**
     *
     * {@inheritdoc}
     */
    public function markAsEnabledById($id)
    {
        $this->userConfig->newQuery()->where('id', $id)->update(['enabled' => true]);
    }

    /**
     *
     * {@inheritdoc}
     */
    public function markAsDisabledById($id)
    {
        $this->userConfig->newQuery()->where('id', $id)->update(['enabled' => false]);
    }

}
