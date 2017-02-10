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






namespace Antares\TwoFactorAuth\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Antares\Logger\Traits\LogRecorder;
use Carbon\Carbon;
use Antares\Model\User;

/**
 * Class UserConfig
 * @property int $id
 * @property int $provider_id
 * @property int $user_id
 * @property bool $enabled
 * @property bool $configured
 * @property array $settings
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Provider $provider
 * @property User $user
 */
class UserConfig extends Eloquent
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
    protected $fillable       = ['provider_id', 'user_id', 'settings'];
    protected $casts          = [
        'id'          => 'integer',
        'provider_id' => 'integer',
        'user_id'     => 'integer',
        'settings'    => 'array',
        'configured'  => 'boolean',
        'enabled'     => 'boolean',
    ];
    protected $attributes     = [
        'configured' => false,
        'enabled'    => false,
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tbl_two_factor_auth_users';

    /**
     * Return a Provider model which belongs to the model.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * Return a User model which belongs to the model.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
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
     * Check if is already configured.
     * 
     * @return boolean
     */
    public function isConfigured()
    {
        return $this->configured;
    }

    /**
     * Checks if models are the same.
     *
     * @param UserConfig $userConfig
     * @return bool
     */
    public function isEquals(UserConfig $userConfig)
    {
        return $this->getId() === $userConfig->getId();
    }

    /**
     * Gets log title
     * 
     * @param mixed $id
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return String
     */
    public static function getLogTitle($id, $model)
    {
        $key                = 'USERCONFIG_' . strtoupper($model->type) . '_PROVIDER';
        $provider           = last($model->related_data['provider']);
        $html               = app('html');
        $params             = [];
        $params['owner_id'] = $html->link(handles('antares::foundation/settings/security'), array_get($provider, 'name'));
        $params['user']     = $html->link(handles('antares::foundation/users/' . $model->user->id), '#' . $model->user->id . ' ' . $model->user->fullname);

        return trans('antares/two_factor_auth::operations.' . $key, $params);
    }

}
