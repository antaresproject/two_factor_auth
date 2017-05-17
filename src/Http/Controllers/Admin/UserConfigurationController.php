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

namespace Antares\Modules\TwoFactorAuth\Http\Controllers\Admin;

use Antares\Contracts\Html\Builder;
use Antares\Modules\TwoFactorAuth\Model\Provider;
use Antares\Modules\TwoFactorAuth\Processor\UserConfigurationProcessor;
use Antares\Modules\TwoFactorAuth\Contracts\UserConfigurationListener;
use Antares\Foundation\Http\Controllers\AdminController;
use Antares\Area\Contracts\AreaContract;
use Antares\Model\User;

class UserConfigurationController extends AdminController implements UserConfigurationListener
{

    /**
     * UserConfigurationController constructor.
     * @param UserConfigurationProcessor $processor
     */
    public function __construct(UserConfigurationProcessor $processor)
    {
        parent::__construct();

        $this->processor = $processor;
    }

    /**
     * Setup middleware based on ACL.
     */
    public function setupMiddleware()
    {
        
    }

    /**
     * Enable the available provider in the area for the user.
     *
     * @param User $user
     * @param AreaContract $area
     * @return mixed
     */
    public function enable(User $user, AreaContract $area)
    {
        request()->session()->set('return_url', request()->headers->get('referer'));

        return $this->processor->enable($this, $user, $area);
    }

    /**
     * Disable the available provider in the area for the user.
     *
     * @param User $user
     * @param AreaContract $area
     * @return mixed
     */
    public function disable(User $user, AreaContract $area)
    {
        return $this->processor->disable($this, $user, $area);
    }

    /**
     * {@inheritdoc}
     */
    public function enableSuccess($msg)
    {
        app('antares.messages')->add('success', $msg);

        $redirectUrl = request()->session()->pull('return_url', handles('two_factor_auth.configuration.index'));

        return redirect()->to($redirectUrl);
    }

    /**
     * {@inheritdoc}
     */
    public function enableFailed($msg)
    {
        app('antares.messages')->add('error', $msg);
        return redirect()->back();
    }

    /**
     * {@inheritdoc}
     */
    public function disableSuccess($msg)
    {
        app('antares.messages')->add('success', $msg);
        return redirect()->back();
    }

    /**
     * {@inheritdoc}
     */
    public function disableFailed($msg)
    {
        app('antares.messages')->add('error', $msg);
        return redirect()->back();
    }

    /**
     *
     * @param AreaContract $area
     * @return mixed
     */
    public function getConfiguration(AreaContract $area)
    {
        return $this->processor->configure($this, $area);
    }

    /**
     * {@inheritdoc}
     */
    public function showConfiguration(Provider $provider, Builder $form)
    {
        $title = $provider->getProviderGateway()->getLabel();

        set_meta('title', $title);

        return view('antares/two_factor_auth::admin.auth.configuration', compact('form'));
    }

    /**
     * Mark an area configuration as configured.
     *
     * @param AreaContract $area
     * @return type
     */
    public function postConfiguration(AreaContract $area)
    {
        return $this->processor->markAsConfigured($this, $area);
    }

    /**
     * {@inheritdoc}
     */
    public function afterConfiguration($form)
    {
        //return $this->enableSuccess($msg);
        return view('antares/two_factor_auth::admin.auth.configuration', compact('form'));
    }

}
