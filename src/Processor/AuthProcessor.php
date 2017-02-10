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






namespace Antares\TwoFactorAuth\Processor;

use Antares\Area\Contracts\AreaContract;
use Antares\TwoFactorAuth\Contracts\AuthListener;
use Antares\TwoFactorAuth\Services\TwoFactorProvidersService;
use Antares\TwoFactorAuth\Services\UserProviderConfigService;
use Antares\TwoFactorAuth\Http\Presenters\AuthPresenter;

class AuthProcessor
{

    /**
     * Auth Presenter instance.
     *
     * @var AuthPresenter 
     */
    protected $presenter;

    /**
     * 2FA Service instance.
     *
     * @var TwoFactorProvidersService
     */
    protected $service;

    /**
     * User provider configuration service instance.
     *
     * @var UserProviderConfigService 
     */
    protected $userConfigService;

    /**
     * AuthProcessor constructor.
     * @param AuthPresenter $presenter
     * @param TwoFactorProvidersService $service
     * @param UserProviderConfigService $userConfigService
     */
    public function __construct(AuthPresenter $presenter, TwoFactorProvidersService $service, UserProviderConfigService $userConfigService)
    {
        $this->presenter         = $presenter;
        $this->service           = $service->bind();
        $this->userConfigService = $userConfigService;
    }

    /**
     * Show verify form for given area.
     *
     * @param AuthListener $listener
     * @param AreaContract $area
     * @return mixed
     */
    public function verify(AuthListener $listener, AreaContract $area)
    {
        $provider   = $this->service->getEnabledInArea($area);
        $userConfig = $this->userConfigService->getSettingsByArea($area);
        $form       = $this->presenter->verify($userConfig, $area, $provider);

        return $listener->showVerifyForm($provider, $form);
    }

    /**
     * Verify provider credentials.
     * 
     * @param AuthListener $listener
     * @param AreaContract $area
     * @param array $input
     * @return mixed
     */
    public function verifyCredentials(AuthListener $listener, AreaContract $area, array $input)
    {
        $provider   = $this->service->getEnabledInArea($area);
        $userConfig = $this->userConfigService->getSettingsByArea($area);

        if (!$provider->getProviderGateway()->isVerified($userConfig, $input)) {
            return $listener->verifyFailed();
        }
        $this->userConfigService->setAsConfigured($userConfig);
        $this->service->getAuthStore()->verify();
        return $listener->authenticate($area);
    }

}
