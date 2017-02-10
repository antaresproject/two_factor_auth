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






namespace Antares\TwoFactorAuth\Contracts;

use Antares\Area\Contracts\AreaContract;
use Antares\TwoFactorAuth\Model\Provider;
use Antares\Contracts\Html\Builder;

interface ConfigurationListener
{

    /**
     * Show configuration page for a provider.
     *
     * @param AreaContract $area
     * @param Provider $provider
     * @param Builder $form
     */
    public function showProviderConfiguration(AreaContract $area, Provider $provider, Builder $form);

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
