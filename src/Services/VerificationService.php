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

namespace Antares\Modules\TwoFactorAuth\Services;

use Antares\Area\Contracts\AreaManagerContract;
use Antares\Modules\TwoFactorAuth\Contracts\UserConfigRepositoryContract;
use Antares\Contracts\Auth\Guard;
use Illuminate\Routing\Route;
use Illuminate\Contracts\Routing\UrlGenerator;

class VerificationService
{

    /**
     * Area manager instance.
     *
     * @var AreaManager
     */
    protected $areaManager;

    /**
     * 2FA providers service instance.
     * @var TwoFactorProvidersService
     */
    protected $service;

    /**
     * User configuration repository instance.
     *
     * @var UserConfigRepositoryContract
     */
    protected $userConfigRepository;

    /**
     * User provider configuration service instance.
     *
     * @var UserProviderConfigService
     */
    protected $userProviderConfigService;

    /**
     * Url generator instance.
     *
     * @var UrlGenerator
     */
    protected $urlGenerator;

    /**
     * VerificationService constructor.
     * @param AreaManagerContract $areaManager
     * @param TwoFactorProvidersService $service
     * @param UserConfigRepositoryContract $userConfigRepository
     * @param UserProviderConfigService $userProviderConfigService
     * @param UrlGenerator $urlGenerator
     */
    public function __construct(AreaManagerContract $areaManager, TwoFactorProvidersService $service, UserConfigRepositoryContract $userConfigRepository, UserProviderConfigService $userProviderConfigService, UrlGenerator $urlGenerator)
    {
        $this->areaManager               = $areaManager;
        $this->service                   = $service;
        $this->userConfigRepository      = $userConfigRepository;
        $this->userProviderConfigService = $userProviderConfigService;
        $this->urlGenerator              = $urlGenerator;
    }

    /**
     * Check if the user/guest must be verified in the given route.
     *
     * @param Route $route
     * @param Guard $guard
     * @return bool
     */
    public function mustBeVerified(Route $route, Guard $guard)
    {
        if ($guard->guest()) {
            $this->service->getAuthStore()->unverify();

            return false;
        }

        if ($this->routeBelongsToComponent($route)) {
            return false;
        }

        if ($this->service->getAuthStore()->isVerified()) {
            return false;
        }

        $currentArea = $this->areaManager->getCurrentArea();
        if (is_string($currentArea)) {
            $currentArea = $this->areaManager->getById($currentArea);
        }


        if ($this->service->bind()->isRequiredInArea($currentArea)) {
            return true;
        }

        $this->userProviderConfigService->setUser($guard->user());

        if ($this->userProviderConfigService->hasEnabledArea($currentArea)) {
            return true;
        }

        return false;
    }

    /**
     * Returns the path to the 2FA verification action.
     *
     * @return Route
     */
    public function getPathToVerificationAction()
    {
        return handles('two_factor_auth.get.verify', ['area' => $this->areaManager->getCurrentArea()]);
    }

    /**
     * Check if the given route has name which belongs to the Two-Factor Authentication component.
     *
     * @param Route $route
     * @return bool
     */
    protected function routeBelongsToComponent(Route $route)
    {
        return strpos($route->getName(), 'two_factor_auth') !== false;
    }

}
