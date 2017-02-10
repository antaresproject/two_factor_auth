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






namespace Antares\TwoFactorAuth\Http\Handlers;

use Antares\Html\Form\FormBuilder;
use Antares\TwoFactorAuth\Processor\ConfigurationProcessor;

class SecuritySection
{

    /**
     * Configuration processor instance.
     *
     * @var ConfigurationProcessor
     */
    protected $configurationProcessor;

    /**
     * SecuritySection constructor.
     * @param ConfigurationProcessor $configurationProcessor
     */
    public function __construct(ConfigurationProcessor $configurationProcessor)
    {
        $this->configurationProcessor = $configurationProcessor;
    }

    /**
     * Handles the simple config of the module.
     *
     * @param array $options
     * @param FormBuilder $form
     */
    public function handle(array $options, $form)
    {
        if ($this->can()) {
            $this->configurationProcessor->index($form);
        }
    }

    /**
     * Checks if the logged user can access to the section.
     *
     * @return bool
     */
    public function can()
    {
        return app('antares.acl')->make('antares/two_factor_auth')->can('configuration');
    }

}
