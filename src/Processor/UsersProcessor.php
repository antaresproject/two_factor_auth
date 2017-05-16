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

namespace Antares\Modules\TwoFactorAuth\Processor;

use Antares\Modules\TwoFactorAuth\Contracts\UsersListener;
use Antares\Modules\TwoFactorAuth\Contracts\UserConfigRepositoryContract;
use Antares\Model\User;
use Exception;
use Log;

class UsersProcessor
{

    /**
     * User configuration repository instance.
     *
     * @var UserConfigRepositoryContract
     */
    protected $userConfigRepository;

    /**
     * UsersProcessor constructor.
     * @param UserConfigRepositoryContract $userConfigRepository
     */
    public function __construct(UserConfigRepositoryContract $userConfigRepository)
    {
        $this->userConfigRepository = $userConfigRepository;
    }

    /**
     * Reset configuration for given user for all providers. Response will be returned.
     * 
     * @param UsersListener $listener
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetUserConfig(UsersListener $listener, User $user)
    {
        try {
            $this->userConfigRepository->deleteByUserId($user->id);
            return $listener->resetSuccess();
        } catch (Exception $e) {
            Log::emergency($e->getMessage());
            return $listener->resetFailed();
        }
    }

}
