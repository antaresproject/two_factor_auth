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
return [
    'di'        => [
        \Antares\Modules\TwoFactorAuth\Contracts\ConfigurationPresenter::class       => \Antares\Modules\TwoFactorAuth\Http\Presenters\ConfigurationPresenter::class,
        \Antares\Modules\TwoFactorAuth\Contracts\ProvidersRepositoryContract::class  => \Antares\Modules\TwoFactorAuth\Repositories\ProvidersRepository::class,
        \Antares\Modules\TwoFactorAuth\Contracts\UserConfigRepositoryContract::class => \Antares\Modules\TwoFactorAuth\Repositories\UserConfigRepository::class,
    ],
    'enabled'   => true,
    'assets'    => [
        'scripts' => [
            'configuration-js' => 'js/configuration.js',
        ]
    ],
    'providers' => [
        [
            'contract' => PragmaRX\Google2FA\Contracts\Google2FA::class,
            'class'    => PragmaRX\Google2FA\Google2FA::class,
            'provider' => Antares\Modules\TwoFactorAuth\Providers\Google2FAProvider::class,
        ],
        [
            'contract' => MarcinKozak\Yubikey\Yubikey::class,
            'class'    => MarcinKozak\Yubikey\Yubikey::class,
            'provider' => Antares\Modules\TwoFactorAuth\Providers\YubikeyProvider::class,
        ],
    ],
];

