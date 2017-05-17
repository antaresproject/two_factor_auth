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

use Illuminate\Contracts\Auth\Authenticatable as User;
use Antares\Model\User as BaseUser;
use Antares\Model\Role;
use Antares\Area\Contracts\AreaContract;
use Antares\Modules\TwoFactorAuth\Contracts\UserConfigRepositoryContract;
use Antares\Modules\TwoFactorAuth\Model\UserConfig;
use Antares\Modules\TwoFactorAuth\Model\Provider;
use Exception;

class UserProviderConfigService
{

    /**
     * User configuration repository instance.
     *
     * @var UserConfigRepositoryContract 
     */
    protected $userConfigRepository;

    /**
     * 2FA providers service instance.
     *
     * @var TwoFactorProvidersService
     */
    protected $service;

    /**
     * User instance.
     *
     * @var User
     */
    protected $user;

    /**
     * User configuration instance.
     *
     * @var UserConfig | null
     */
    protected $userConfig;

    /**
     * UserProviderConfigService constructor.
     * @param UserConfigRepositoryContract $userConfigRepository
     * @param TwoFactorProvidersService $service
     * @param User|null $user
     */
    public function __construct(UserConfigRepositoryContract $userConfigRepository, TwoFactorProvidersService $service, User $user = null)
    {
        $this->userConfigRepository = $userConfigRepository;
        $this->service              = $service->bind();
        $this->user                 = $user;
    }

    /**
     * Sets the user to the service.
     *
     * @param BaseUser $user
     * @return \Antares\Modules\TwoFactorAuth\Services\UserProviderConfigService
     */
    public function setUser(BaseUser $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Returns the user config model of the given area instance.
     *
     * @param AreaContract $area
     * @return UserConfig | null
     * @throws Exception
     */
    public function getSettingsByArea(AreaContract $area)
    {
        $provider = $this->service->getEnabledInArea($area);

        if ($provider === null) {
            throw new Exception('There is no enabled provider for the ' . $area->getLabel() . '.');
        }

        $this->userConfig = $this->userConfigRepository->findByUserIdAndProviderId($this->user->id, $provider->id);

        return $this->userConfig;
    }

    /**
     * Check if the provider for the given area has been configured already.
     *
     * @param AreaContract $area
     * @return boolean
     */
    public function hasConfiguredArea(AreaContract $area)
    {
        try {
            $userConfig = $this->getSettingsByArea($area);

            return $userConfig AND $userConfig->isConfigured();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check if the provider for the given area has been enabled already.
     *
     * @param AreaContract $area
     * @return bool
     */
    public function hasEnabledArea(AreaContract $area)
    {
        try {
            $userConfig = $this->getSettingsByArea($area);

            return $userConfig AND $userConfig->isEnabled();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Checks if the before assigned user has any configured areas.
     *
     * @return bool
     */
    public function hasAnyConfiguredAreas()
    {
        $userConfig = $this->userConfigRepository->findByUserId($this->user->id);

        return $userConfig AND $userConfig->isConfigured();
    }

    /**
     * Stores the settings of user configuration.
     *
     * @param Provider $provider
     * @return UserConfig
     */
    public function saveConfig(Provider $provider)
    {
        $data = [
            'user_id'     => $this->user->id,
            'provider_id' => $provider->id,
            'settings'    => $provider->getProviderGateway()->getConfigSettings(),
        ];

        return $this->userConfigRepository->save($data);
    }

    /**
     * Marks the given user configuration as configured.
     *
     * @param UserConfig $userConfig
     */
    public function setAsConfigured(UserConfig $userConfig)
    {
        $this->userConfigRepository->markAsConfiguredById($userConfig->id);
    }

    /**
     * Returns supported areas by the user.
     *
     * @return AreaContract[]
     * @throws Exception
     */
    public function getSupportedAreas()
    {
        if ($this->user === null) {
            throw new Exception('No user has been provided to the service.');
        }
        $admin  = Role::admin()->name;
        $member = Role::member()->name;

        $supportAreaIds = [];
        $usedAreas      = [];

        if ($this->user->isAny([$admin])) {
            $supportAreaIds[] = 'admin';
        }

        if ($this->user->isAny([$member])) {
            $supportAreaIds[] = 'client';
        }

        $areas = $this->service->getAreaManager()->getAreas();

        foreach ($areas as $area) {
            if (in_array($area->getId(), $supportAreaIds)) {
                $usedAreas[] = $area;
            }
        }

        return $usedAreas;
    }

}
