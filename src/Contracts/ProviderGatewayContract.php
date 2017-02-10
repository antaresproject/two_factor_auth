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

use Antares\Contracts\Html\Form\Fieldset;
use Antares\TwoFactorAuth\Model\UserConfig;
use Antares\TwoFactorAuth\Model\Provider;
use Antares\TwoFactorAuth\Validators\ProviderValidator;

interface ProviderGatewayContract
{
    
    /**
     * Returns an internal name of a provider.
     * 
     * @return string
     */
    public function getName();
    
    /**
     * Returns a friendly-user label.
     * 
     * @return string
     */
    public function getLabel();

    /**
     * Returns an icon name.
     *
     * @return string
     */
    public function getIconName();

    /**
     * Returns an icon absolute URL.
     *
     * @return string
     */
    public function getIconUrl();
    
    /**
     * Generate a fieldset which allows to set up a provider configuration on the first use.
     * 
     * @param Fieldset $fieldset
     * @param UserConfig $userConfig
     */
    public function setupFrontendFormFieldset(Fieldset $fieldset, UserConfig $userConfig);
    
    /**
     * Generate a fieldset for verification form.
     * 
     * @param Fieldset $fieldset
     */
    public function setupVerifyFormFieldset(Fieldset $fieldset);
    
    /**
     * Generate a fieldset which allows to set up a provider configuration.
     * 
     * @param Provider $provider
     * @param Fieldset $fieldset
     */
    public function setupBackendFormFieldset(Provider $provider, Fieldset $fieldset);
    
    /**
     * Return an array of settings which will be stored in database for user config.
     * 
     * @return []
     */
    public function getConfigSettings();
    
    /**
     * Check if user is verified by provided data.
     * 
     * @param UserConfig $userConfig
     * @param array $data
     * @return boolean
     */
    public function isVerified(UserConfig $userConfig, array $data);

    /**
     * Returns the validator instance.
     *
     * @return ProviderValidator
     */
    public function getValidator();
    
}
