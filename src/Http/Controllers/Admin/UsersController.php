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

namespace Antares\Modules\TwoFactorAuth\Http\Controllers\Admin;

use Antares\Foundation\Http\Controllers\AdminController;
use Antares\Modules\TwoFactorAuth\Contracts\UsersListener;
use Antares\Modules\TwoFactorAuth\Processor\UsersProcessor;
use Antares\Model\User;

class UsersController extends AdminController implements UsersListener
{

    /**
     * Users processor instance.
     *
     * @var UsersProcessor
     */
    protected $processor;

    /**
     * UsersController constructor.
     * @param UsersProcessor $processor
     */
    public function __construct(UsersProcessor $processor)
    {
        parent::__construct();

        $this->processor = $processor;
    }

    /**
     * Setup middleware based on ACL.
     */
    protected function setupMiddleware()
    {
        
    }

    /**
     * Reset a provider configuration which belongs to a provider user ID.
     * 
     * @param User $user
     * @return mixed
     */
    public function getReset(User $user)
    {
        return $this->processor->resetUserConfig($this, $user);
    }

    /**
     * {@inheritdoc}
     */
    public function resetFailed()
    {
        $message = trans('antares/two_factor_auth::users.responses.reset.fail');
        app('antares.messages')->add('error', $message);
        return redirect()->back();
    }

    /**
     * {@inheritdoc}
     */
    public function resetSuccess()
    {
        $message = trans('antares/two_factor_auth::users.responses.reset.success');
        app('antares.messages')->add('success', $message);
        return redirect()->back();
    }

}
