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

namespace Antares\Modules\TwoFactorAuth\Processor;

use Antares\Modules\TwoFactorAuth\Http\Presenters\UserConfigurationPresenter;
use Antares\Modules\TwoFactorAuth\Contracts\UserConfigurationListener;
use Antares\Modules\TwoFactorAuth\Contracts\UserConfigRepositoryContract;
use Antares\Area\Contracts\AreaContract;
use Antares\Modules\TwoFactorAuth\Model\UserConfig;
use Antares\Modules\TwoFactorAuth\Services\TwoFactorProvidersService;
use Antares\Modules\TwoFactorAuth\Services\UserProviderConfigService;
use Antares\Model\User;
use Illuminate\Events\Dispatcher;
use Exception;
use Log;

class UserConfigurationProcessor
{

    /**
     * Dispatcher instance.
     *
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * 2FA providers service instance.
     *
     * @var TwoFactorProvidersService
     */
    protected $twoFactorProvidersService;

    /**
     * User provider configuration service instance.
     *
     * @var UserProviderConfigService
     */
    protected $userConfigService;

    /**
     * User configuration presenter instance.
     *
     * @var UserConfigurationPresenter
     */
    protected $presenter;

    /**
     * User configuration repository instance.
     *
     * @var UserConfigRepositoryContract
     */
    protected $userConfigRepository;

    /**
     * UserConfigurationProcessor constructor.
     * @param Dispatcher $dispatcher
     * @param UserConfigurationPresenter $presenter
     * @param TwoFactorProvidersService $twoFactorProvidersService
     * @param UserProviderConfigService $userConfigService
     * @param UserConfigRepositoryContract $userConfigRepository
     */
    public function __construct(Dispatcher $dispatcher, UserConfigurationPresenter $presenter, TwoFactorProvidersService $twoFactorProvidersService, UserProviderConfigService $userConfigService, UserConfigRepositoryContract $userConfigRepository)
    {
        $this->dispatcher                = $dispatcher;
        $this->presenter                 = $presenter;
        $this->twoFactorProvidersService = $twoFactorProvidersService->bind();
        $this->userConfigService         = $userConfigService;
        $this->userConfigRepository      = $userConfigRepository;
    }

    /**
     * Check if a user has configured authentication for given area.
     *
     * @param AreaContract $area
     * @return boolean
     */
    public function isConfigured(AreaContract $area)
    {
        return $this->userConfigService->hasConfiguredArea($area);
    }

    /**
     * Show configuration form for given area.
     *
     * @param UserConfigurationListener $listener
     * @param AreaContract $area
     * @return type
     */
    public function configure(UserConfigurationListener $listener, AreaContract $area)
    {
        $provider   = $this->twoFactorProvidersService->getEnabledInArea($area);
        $userConfig = $this->userConfigService->saveConfig($provider);

        $form = $this->presenter->configure($userConfig, $area, $provider);
        $this->dispatcher->fire('antares.form: two_factor_auth', [$provider, $form]);

        return $listener->showConfiguration($provider, $form);
    }

    /**
     * Mark given area as configured.
     *
     * @param UserConfigurationListener $listener
     * @param AreaContract $area
     * @return type
     */
    public function markAsConfigured(UserConfigurationListener $listener, AreaContract $area)
    {
        /** @var $service TwoFactorProvidersService */
        $service  = app(TwoFactorProvidersService::class);
        $service->bind();
        $provider = $service->getEnabledInArea($area);

        if ($id = (\Input::all()['user_id']) ?? null) {
            $userConfig = app(UserConfig::class)
                    ->find($id);

            $user = app(User::class)
                    ->find($userConfig->user_id);
        }

        $this->userConfigService->setUser($user ?? auth()->user());

        $userConfig = $this->userConfigService->getSettingsByArea($area);
        $secretKey  = $userConfig->settings['secret_key'];
        $form       = app(\Antares\Modules\TwoFactorAuth\Http\Presenters\AuthPresenter::class)
                ->verify($userConfig, $area, $provider, $secretKey);

        $this->userConfigService->setAsConfigured($userConfig);
        //$msg = trans('antares/two_factor_auth::configuration.responses.enable.success', ['area' => $area->getLabel()]);

        return $listener->afterConfiguration($form);
    }

    /**
     * Enable the provider for the user in the selected area.
     *
     * @param UserConfigurationListener $listener
     * @param User $user
     * @param AreaContract $area
     * @return mixed
     */
    public function enable(UserConfigurationListener $listener, User $user, AreaContract $area)
    {
        try {
            $userConfig = $this->getUserConfig($user, $area);
            $this->userConfigRepository->markAsEnabledById($userConfig->id);
            $this->userConfigService->setUser($user);

            if (!$userConfig->isConfigured()) {
                return $this->configure($listener, $area);
            }

            $msg = trans('antares/two_factor_auth::configuration.responses.enable.success', ['area' => $area->getLabel()]);

            return $listener->enableSuccess($msg);
        } catch (Exception $e) {
            Log::error($e);
            $msg = trans('antares/two_factor_auth::configuration.responses.enable.fail', ['area' => $area->getLabel()]);

            return $listener->enableFailed($msg);
        }
    }

    /**
     * Disable the provider for the user in the selected area.
     *
     * @param UserConfigurationListener $listener
     * @param User $user
     * @param AreaContract $area
     * @return mixed
     */
    public function disable(UserConfigurationListener $listener, User $user, AreaContract $area)
    {
        try {
            $userConfig = $this->getUserConfig($user, $area);

            $this->userConfigRepository->markAsDisabledById($userConfig->id);

            $msg = trans('antares/two_factor_auth::configuration.responses.disable.success', ['area' => $area->getLabel()]);

            return $listener->disableSuccess($msg);
        } catch (Exception $e) {
            Log::error($e);

            $msg = trans('antares/two_factor_auth::configuration.responses.disable.fail', ['area' => $area->getLabel()]);

            return $listener->disableFailed($msg);
        }
    }

    /**
     * Returns user configuration from the repository. It will be stored in repository if it has not been stored yet.
     *
     * @param User $user
     * @param AreaContract $area
     * @return \Antares\Modules\TwoFactorAuth\Contracts\UserConfig
     */
    protected function getUserConfig(User $user, AreaContract $area)
    {
        $provider   = $this->twoFactorProvidersService->getEnabledInArea($area);
        $userConfig = $this->userConfigRepository->findByUserIdAndProviderId($user->id, $provider->getId());

        if ($userConfig) {
            return $userConfig;
        }

        $data = [
            'user_id'     => $user->id,
            'provider_id' => $provider->getId(),
        ];

        return $this->userConfigRepository->save($data);
    }

}
