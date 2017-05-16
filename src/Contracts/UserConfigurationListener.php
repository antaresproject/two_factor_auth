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

use Antares\Modules\TwoFactorAuth\Model\Provider;
use Antares\Contracts\Html\Builder;
use Antares\Area\Contracts\AreaContract;

interface UserConfigurationListener
{

    /**
     * Set success message for enable action.
     *
     * @param string $msg
     * @return mixed
     */
    public function enableSuccess($msg);

    /**
     * Set failure message for enable action.
     *
     * @param string $msg
     * @return mixed
     */
    public function enableFailed($msg);

    /**
     * Set success message for disable action.
     *
     * @param string $msg
     * @return mixed
     */
    public function disableSuccess($msg);

    /**
     * Set failure message for disable action.
     *
     * @param string $msg
     * @return mixed
     */
    public function disableFailed($msg);

    /**
     * Show a configuration page for the chosen provider.
     *
     * @param Provider $provider
     * @param Builder $form
     */
    public function showConfiguration(Provider $provider, Builder $form);

    /**
     * Handle listener after the configuration page.
     *
     * @param AreaContract $area
     * @param string $msg
     * @return mixed
     */
    //public function afterConfiguration(AreaContract $area, $msg);
}
