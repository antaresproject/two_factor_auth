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
use Antares\Contracts\Html\Builder;
use Antares\Modules\TwoFactorAuth\Model\Provider;

interface ConfigurationListener
{

    /**
     * Show configuration page for a provider.
     *
     * @param Area $area
     * @param Provider $provider
     * @param Builder $form
     */
    public function showProviderConfiguration(Area $area, Provider $provider, Builder $form);

    /**
     * Set failure message.
     *
     * @param string $msg
     * @return mixed
     */
    public function updateFailed($msg);

    /**
     * Set success message.
     *
     * @param string $msg
     * @return mixed
     */
    public function updateSuccess($msg);
}
