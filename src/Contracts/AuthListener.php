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

use Antares\Area\Model\Area;
use Antares\Modules\TwoFactorAuth\Model\Provider;
use Antares\Contracts\Html\Builder;

interface AuthListener
{

    /**
     * Show a verify form.
     * 
     * @param Provider $provider
     * @param Builder $form
     */
    public function showVerifyForm(Provider $provider, Builder $form);

    /**
     * Redirect to URL based on an area.
     * 
     * @param Area $area
     */
    public function authenticate(Area $area);

    /**
     * 
     * @param String $area
     * @param boolean $withError (default false)
     */
    public function getVerify($area, $withError = false);
}
