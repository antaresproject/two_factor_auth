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

namespace Antares\Modules\TwoFactorAuth\Contracts;

interface ProvidersRepositoryContract
{

    /**
     * Return all providers.
     * 
     * @return Providers[]
     */
    public function all();

    /**
     * Find a provider by its ID.
     * 
     * @param int $providerId
     * @return Provider
     */
    public function findById($providerId);

    /**
     * Update the providers.
     *
     * @param array $data
     * @throws Exception
     */
    public function update(array $data);
}
