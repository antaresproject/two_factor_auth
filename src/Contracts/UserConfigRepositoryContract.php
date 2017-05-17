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

namespace Antares\Modules\TwoFactorAuth\Contracts;

use Antares\Modules\TwoFactorAuth\Model\UserConfig;

interface UserConfigRepositoryContract
{

    /**
     *  Create or update (if exists) configuration for given userId and providerId in array.
     * 
     * @param array $data
     * @return UserConfig
     */
    public function save(array $data);

    /**
     * Delete records by a user ID.
     * 
     * @param int $userId
     */
    public function deleteByUserId($userId);

    /**
     * Find the first record by a user ID and a provider ID.
     * 
     * @param int $userId
     * @param int $providerId
     * @return UserConfig | null
     */
    public function findByUserIdAndProviderId($userId, $providerId);

    /**
     * Find the first record by a user ID.
     * 
     * @param int $userId
     * @return UserConfig | null
     */
    public function findByUserId($userId);

    /**
     * Find a record based on its ID and mark it as configured.
     * 
     * @param int $id
     */
    public function markAsConfiguredById($id);

    /**
     * Find a record based on its ID and mark it as enabled.
     *
     * @param int $id
     */
    public function markAsEnabledById($id);

    /**
     * Find a record based on its ID and mark it as disabled.
     *
     * @param int $id
     */
    public function markAsDisabledById($id);
}
