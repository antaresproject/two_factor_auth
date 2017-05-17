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

namespace Antares\Modules\TwoFactorAuth\Listeners;

use Antares\Foundation\Events\SecurityFormSubmitted;
use Antares\Modules\TwoFactorAuth\Contracts\ProvidersRepositoryContract;
use Exception;
use Log;

class SecurityFormListener
{

    /**
     * Providers repository instance.
     *
     * @var ProvidersRepositoryContract
     */
    protected $providersRepository;

    /**
     * SecurityFormListener constructor.
     * @param ProvidersRepositoryContract $providersRepository
     */
    public function __construct(ProvidersRepositoryContract $providersRepository)
    {
        $this->providersRepository = $providersRepository;
    }

    /**
     * Handles the security form event.
     *
     * @param SecurityFormSubmitted $securityFormSubmitted
     */
    public function handle(SecurityFormSubmitted $securityFormSubmitted)
    {
        $data = $securityFormSubmitted->request->get('2fa', []);
        try {
            $this->providersRepository->update($data);
            $msg = trans('antares/two_factor_auth::configuration.responses.update.success');
            return $securityFormSubmitted->listener->onSuccess($msg);
        } catch (Exception $e) {
            Log::emergency($e->getMessage());
            return $securityFormSubmitted->listener->onFail($e->getMessage());
        }
    }

}
