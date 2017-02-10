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






namespace Antares\TwoFactorAuth\Providers;

use PragmaRX\Google2FA\Contracts\Google2FA;
use Antares\Contracts\Html\Form\Fieldset;
use Antares\TwoFactorAuth\Model\UserConfig;
use Antares\TwoFactorAuth\Validators\Google2FAValidator;
use HTML;

class Google2FAProvider extends ProviderGateway
{

    /**
     * Google factory instance.
     *
     * @var Google2FA
     */
    protected $googleFactory;

    /**
     * Validator instance.
     *
     * @var Google2FAValidator
     */
    protected $validator;

    /**
     * Google2FAProvider constructor.
     * @param Google2FA $googleFactory
     * @param Google2FAValidator $validator
     */
    public function __construct(Google2FA $googleFactory, Google2FAValidator $validator)
    {
        $this->googleFactory = $googleFactory;
        $this->validator     = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'google2fa';
    }

    /**
     * {@inheritdoc}
     */
    public function getIconName()
    {
        return 'icon-google_2fa.png';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return trans('antares/two_factor_auth::google2fa.label');
    }

    /**
     * {@inheritdoc}
     */
    public function setupFrontendFormFieldset(Fieldset $fieldset, UserConfig $userConfig)
    {
        $fieldset->control('input:text', 'qrcode')->field(function() use($userConfig) {
            $email  = $userConfig->user->email;
            $secret = array_get($userConfig->settings, 'secret_key');
            $url    = $this->googleFactory->getQRCodeGoogleUrl(brand_name(), $email, $secret);
            $alt    = trans('antares/two_factor_auth::google2fa.qrcode');
            $attrs  = ['style' => 'margin:auto'];

            return HTML::image($url, $alt, $attrs);
        })->label(trans('antares/two_factor_auth::google2fa.scan_qrcode'));
    }

    /**
     * {@inheritdoc}
     */
    public function setupVerifyFormFieldset(Fieldset $fieldset)
    {
        $fieldset->control('input:text', 'verification_code')->label(trans('antares/two_factor_auth::google2fa.confirm_code_label'));
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigSettings()
    {
        return [
            'secret_key' => $this->googleFactory->generateSecretKey(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function isVerified(UserConfig $userConfig, array $data)
    {
        $userSecretKey    = array_get($userConfig->settings, 'secret_key');
        $verificationCode = array_get($data, 'verification_code');
        return $this->googleFactory->verifyKey($userSecretKey, $verificationCode, 2000);
    }

    /**
     * @return Google2FAValidator
     */
    public function getValidator()
    {
        return $this->validator;
    }

}
