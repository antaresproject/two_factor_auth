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
use Antares\Modules\TwoFactorAuth\Contracts\ProviderGatewayContract;
use Antares\Modules\TwoFactorAuth\Facades\AreaProviders;
use Illuminate\Support\Collection;
use Antares\Modules\TwoFactorAuth\Collection\ProvidersCollection;
use Antares\Modules\TwoFactorAuth\Model\Provider;
use Antares\Modules\TwoFactorAuth\Contracts\ProvidersRepositoryContract;
use Antares\Area\Contracts\AreaContract;
use Antares\Area\AreaManager;

class TwoFactorProvidersService
{

    /**
     * Area manager instance.
     *
     * @var AreaManagerContract
     */
    protected $areaManager;

    /**
     * Providers repository instance.
     *
     * @var ProvidersRepositoryContract
     */
    protected $providersRepository;

    /**
     * Auth store instance.
     *
     * @var AuthStore
     */
    protected $authStore;

    /**
     * Providers collection instance.
     *
     * @var ProvidersCollection
     */
    protected $providers;

    /**
     * Array of providers gateways.
     *
     * @var ProviderGatewayContract[]
     */
    protected $providerGateways = [];

    /**
     * Flag fo runtime cache.
     *
     * @var boolean
     */
    protected $providersLoaded = false;

    /**
     * TwoFactorProvidersService constructor.
     * @param AreaManagerContract $areaManager
     * @param ProvidersRepositoryContract $providersRepository
     * @param \Antares\Modules\TwoFactorAuth\Services\AuthStore $authStore
     */
    public function __construct(AreaManagerContract $areaManager, ProvidersRepositoryContract $providersRepository, AuthStore $authStore)
    {
        $this->areaManager         = $areaManager;
        $this->providersRepository = $providersRepository;
        $this->authStore           = $authStore;
        $this->providers           = new ProvidersCollection;
    }

    /**
     * Returns the Auth Store instance.
     *
     * @return AuthStore
     */
    public function getAuthStore()
    {
        return $this->authStore;
    }

    /**
     * Bind gateways with providers models.
     * 
     * @return \Antares\Modules\TwoFactorAuth\Services\TwoFactorProvidersService
     */
    public function bind()
    {
        if ($this->providersLoaded) {
            return $this;
        }

        $this->providers = new ProvidersCollection($this->providersRepository->all());

        foreach ($this->providerGateways as & $providerGateway) {
            $providers = $this->providers->filterByGatewayName($providerGateway->getName());

            foreach ($providers as & $provider) {
                $provider->setProviderGateway($providerGateway);
            }
        }

        $this->providersLoaded = true;

        return $this;
    }

    /**
     * Adds a new provider gateway to the collection.
     *
     * @param ProviderGatewayContract $providerGateway
     */
    public function addProviderGateway(ProviderGatewayContract $providerGateway)
    {
        $this->providerGateways[] = $providerGateway;
    }

    /**
     * Returns the Area Manager instance.
     *
     * @return AreaManagerContract
     */
    public function getAreaManager()
    {
        return $this->areaManager;
    }

    /**
     * Returns the collection of providers gateways.
     *
     * @return ProviderGatewayContract[]
     */
    public function getProviderGateways()
    {
        return $this->providerGateways;
    }

    /**
     * Returns the collection of providers.
     *
     * @return ProvidersCollection
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * Returns the provider gateway by the given name.
     *
     * @param string $name
     * @return ProviderGatewayContract
     */
    public function getProviderGatewayByName($name)
    {
        foreach ($this->providerGateways as $provider) {
            if ($provider->getName() === $name) {
                return $provider;
            }
        }

        return null;
    }

    /**
     * Returns the collection with areas and providers for each one.
     *
     * @return Collection
     */
    public function getAreaProvidersCollection()
    {
        $collection = new Collection;
        foreach ($this->providers as $model) {
            $area = $this->areaManager->getById($model->area);
            if (is_null($area)) {
                continue;
            }
            $areaProviders = $collection->get($area->getId());
            if ($areaProviders === null) {
                $areaProviders = new AreaProviders($area);
                $areaProviders->addModel($model);
                $collection->put($area->getId(), $areaProviders);
            } else {
                $areaProviders->addModel($model);
            }
        }

        return $collection;
    }

    /**
     * Returns the enabled provider for the given area.
     *
     * @param AreaContract $area
     * @return Provider | null
     */
    public function getEnabledInArea(AreaContract $area)
    {

        $providers = $this->providers->filterByArea($area->getId());
        foreach ($providers as $provider) {
            if ($provider->isEnabled()) {
                return $provider;
            }
        }

        return null;
    }

    /**
     * Check if authentication is required for the given area.
     *
     * @param String $area
     * @return boolean
     */
    public function isRequiredInArea(AreaContract $area)
    {

        $enabledProvider = $this->getEnabledInArea($area);

        if ($enabledProvider === null) {
            return false;
        }

        return $enabledProvider->isForced();
    }

}
