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

interface UsersListener
{

    /**
     * Set success message and redirect to the previous request.
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetSuccess();

    /**
     * Set failure message and redirect to the previous request.
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetFailed();
}
