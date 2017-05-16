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
use Illuminate\Support\Collection;
use Antares\Html\Form\FormBuilder;

interface ConfigurationPresenter
{

    /**
     * Return a View instance for areas configuration page.
     * 
     * @param Collection $areaProvidersCollection
     * @param FormBuilder $form
     * @return View
     */
    public function index(Collection $areaProvidersCollection, FormBuilder $form);

    /**
     * Form generator for a provider configuration page.
     * 
     * @param Provider $provider
     * @return FormGrid
     */
    public function form(Provider $provider);
}
