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

namespace Antares\Modules\TwoFactorAuth\Providers;

use Antares\Modules\TwoFactorAuth\Services\TwoFactorProvidersService;
use MarcinKozak\Yubikey\Yubikey;
use Antares\Contracts\Html\Form\Fieldset;
use Antares\Modules\TwoFactorAuth\Model\UserConfig;
use Antares\Modules\TwoFactorAuth\Model\Provider;
use Antares\Modules\TwoFactorAuth\Validators\YubikeyValidator;
use Exception;

/**
 * Dump provider for Yubikey. Only for preview purpose.
 */
class YubikeyProvider extends ProviderGateway
{

    /**
     * Validator instance.
     *
     * @var YubikeyValidator
     */
    protected $validator;

    /**
     * 2FA providers service instance.
     *
     * @var TwoFactorProvidersService
     */
    protected $twoFactorProvidersService;

    /**
     * YubikeyProvider constructor.
     * @param TwoFactorProvidersService $twoFactorProvidersService
     * @param YubikeyValidator $validator
     */
    public function __construct(TwoFactorProvidersService $twoFactorProvidersService, YubikeyValidator $validator)
    {
        $this->twoFactorProvidersService = $twoFactorProvidersService;
        $this->validator                 = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'yubikey';
    }

    /**
     * {@inheritdoc}
     */
    public function getIconName()
    {
        return 'icon-yubikey.png';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return trans('antares/two_factor_auth::yubikey.label');
    }

    /**
     * {@inheritdoc}
     */
    public function setupFrontendFormFieldset(Fieldset $fieldset, UserConfig $userConfig)
    {
        $fieldset->control('input:text', 'usb')->field(function() {
            return '';
        })->label(trans('antares/two_factor_auth::yubikey.usbKeyPrompt'));
    }

    /**
     * {@inheritdoc}
     */
    public function setupVerifyFormFieldset(Fieldset $fieldset)
    {
        $fieldset->control('input:text', 'verification_code')->label(trans('antares/two_factor_auth::google2fa.verificationCode'));
    }

    /**
     * {@inheritdoc}
     */
    public function setupBackendFormFieldset(Provider $provider, Fieldset $fieldset)
    {
        $settings = array_get($provider, 'settings', []);
        $areaId   = $provider->getAreaId();

        $fieldset->control('input:text', $this->getAreaField($areaId, 'client_id'))
                ->value(array_get($settings, 'client_id'))
                ->label(trans('antares/two_factor_auth::yubikey.backend.client_id'));

        $fieldset->control('input:text', $this->getAreaField($areaId, 'secret_key'))
                ->value(array_get($settings, 'secret_key'))
                ->label(trans('antares/two_factor_auth::yubikey.backend.secret_key'));
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigSettings()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function isVerified(UserConfig $userConfig, array $data)
    {
        $verificationCode = array_get($data, 'verification_code');

        try {
            return $this->getProviderFactoryBasedOnConfig($userConfig)->verify($verificationCode);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Returns the validator.
     *
     * @return YubikeyValidator
     */
    public function getValidator()
    {
        return $this->validator;
    }

    /**
     * Setups the Yubikey factory based on area settings.
     *
     * @param UserConfig $userConfig
     * @return Yubikey
     */
    protected function getProviderFactoryBasedOnConfig(UserConfig $userConfig)
    {
        $areaId   = $userConfig->provider->getAreaId();
        $area     = $this->twoFactorProvidersService->getAreaManager()->getById($areaId);
        $provider = $this->twoFactorProvidersService->getEnabledInArea($area);

        $data = [
            'id'  => array_get($provider->settings, 'client_id'),
            'key' => array_get($provider->settings, 'secret_key'),
        ];

        return new Yubikey($data);
    }

}
