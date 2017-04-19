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

use Antares\TwoFactorAuth\Contracts\ConfigurationPresenter;
use Antares\TwoFactorAuth\Contracts\ConfigurationListener;
use Antares\TwoFactorAuth\Contracts\ProvidersRepositoryContract;
use Antares\Area\Contracts\AreaContract;
use Antares\TwoFactorAuth\Model\Provider;
use Antares\TwoFactorAuth\Services\TwoFactorProvidersService;
use Event;
use Exception;
use Log;

class ConfigurationProcessor
{

    /**
     * 2FA providers service instance.
     *
     * @var TwoFactorProvidersService 
     */
    protected $twoFactorProvidersService;

    /**
     * Configuration presenter instance.
     *
     * @var ConfigurationPresenter
     */
    protected $presenter;

    /**
     * Providers repository instance.
     *
     * @var ProvidersRepositoryContract
     */
    protected $providersRepository;

    /**
     * ConfigurationProcessor constructor.
     * @param ConfigurationPresenter $presenter
     * @param TwoFactorProvidersService $twoFactorProvidersService
     * @param ProvidersRepositoryContract $providersRepository
     */
    public function __construct(ConfigurationPresenter $presenter, TwoFactorProvidersService $twoFactorProvidersService, ProvidersRepositoryContract $providersRepository)
    {
        $this->presenter                 = $presenter;
        $this->twoFactorProvidersService = $twoFactorProvidersService->bind();
        $this->providersRepository       = $providersRepository;
    }

    /**
     * Return a View object based on Presenter.
     * 
     * @return \Illuminate\View\View
     */
    public function index($form = null)
    {
        $areaProviders = $this->twoFactorProvidersService->getAreaProvidersCollection();
        if (is_null($form)) {
            $provider = app(Provider::class)->where('name', 'google2fa')->where('area', area())->first();

            $provider->setProviderGateway($this->twoFactorProvidersService->getProviderGatewayByName($provider->name));
            $form = $this->presenter->form($provider);
        }

        return $this->presenter->index($areaProviders, $form);
    }

    /**
     * Show edit form for given area and provider.
     *
     * @param ConfigurationListener $listener
     * @param AreaContract $area
     * @param Provider $provider
     * @return mixed
     */
    public function edit(ConfigurationListener $listener, AreaContract $area, Provider $provider)
    {
        $provider->area    = $area->getId();
        $provider->enabled = true;

        $provider->setProviderGateway($this->twoFactorProvidersService->getProviderGatewayByName($provider->name));

        $form = $this->presenter->form($provider);

        Event::fire("antares.form: two_factor_auth", [$provider, $form]);
        Event::fire("antares.form: foundation.two_factor_auth", [$provider, $form, "foundation.two_factor_auth"]);

        return $listener->showProviderConfiguration($area, $provider, $form);
    }

    /**
     * Update a configuration. The provider will be marked as enabled. Response will be returned.
     * 
     * @param ConfigurationListener $listener
     * @param array $input
     * @return mixed
     */
    public function update(ConfigurationListener $listener, array $input)
    {
        try {
            $this->providersRepository->update($input);
            $msg = trans('antares/two_factor_auth::configuration.responses.update.success');

            return $listener->updateSuccess($msg);
        } catch (Exception $e) {
            Log::emergency($e);

            $msg = trans('antares/two_factor_auth::configuration.responses.update.fail');
            return $listener->updateFailed($msg);
        }
    }

}
