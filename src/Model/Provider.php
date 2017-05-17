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

namespace Antares\Modules\TwoFactorAuth\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Antares\Logger\Traits\LogRecorder;
use Antares\Modules\TwoFactorAuth\Contracts\ProviderGatewayContract;
use Antares\Model\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Provider
 * @property int $id
 * @property string $name
 * @property string $area
 * @property bool $enabled
 * @property bool $forced
 * @property array $settings
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Collection|UserConfig[] $userConfig
 * @property Collection|User[] $users
 */
class Provider extends Eloquent
{

    use LogRecorder;

    // Disables the log record in this model.
    protected $auditEnabled   = true;
    // Disables the log record after 500 records.
    protected $historyLimit   = 500;
    // Fields you do NOT want to register.
    protected $dontKeepLogOf  = ['created_at', 'updated_at', 'deleted_at'];
    // Tell what actions you want to audit.
    protected $auditableTypes = ['created', 'saved', 'deleted'];
    protected $fillable       = ['name', 'settings', 'enabled', 'area', 'forced'];
    protected $casts          = [
        'id'       => 'integer',
        'enabled'  => 'boolean',
        'forced'   => 'boolean',
        'settings' => 'array',
    ];
    protected $attributes     = [
        'enabled' => false,
        'forced'  => false,
    ];

    /**
     * Provider gateway instance.
     *
     * @var ProviderGatewayContract
     */
    protected $providerGateway;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tbl_two_factor_auth_providers';

    /**
     * Returns users configurations which belong to the model.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function usersConfig()
    {
        return $this->hasMany(UserConfig::class);
    }

    /**
     * Returns users which belong to the model through its configuration.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function users()
    {
        return $this->hasManyThrough(User::class, UserConfig::class);
    }

    /**
     * Associate a Provider Gateway with the model.
     * 
     * @param ProviderGatewayContract $providerGateway
     */
    public function setProviderGateway(ProviderGatewayContract $providerGateway)
    {
        $this->providerGateway = $providerGateway;
    }

    /**
     * Returns an associated Provider Gateway of the model.
     * 
     * @return ProviderGatewayContract
     */
    public function getProviderGateway()
    {
        return $this->providerGateway;
    }

    /**
     * Returns an identifier of the model.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Checks if a provider is enabled.
     * 
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Checks if a provider is forced.
     *
     * @return boolean
     */
    public function isForced()
    {
        return $this->forced;
    }

    /**
     * Returns a name of the provider.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns a name (as identifier) of the area.
     *
     * @return string
     */
    public function getAreaId()
    {
        return $this->area;
    }

    /**
     * Checks if models are the same.
     *
     * @param Provider $provider
     * @return bool
     */
    public function isEquals(Provider $provider)
    {
        return $this->getId() === $provider->getId();
    }

}
