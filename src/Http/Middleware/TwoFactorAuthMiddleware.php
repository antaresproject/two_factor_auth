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






namespace Antares\TwoFactorAuth\Http\Middleware;

use Antares\TwoFactorAuth\Services\VerificationService;
use Antares\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Closure;
use function response;
use function redirect;

class TwoFactorAuthMiddleware {
    
    
    /**
     *  Verification service instance.
     *
     * @var VerificationService
     */
    protected $verificationService;
    
    /**
     * Guard instance.
     *
     * @var Guard
     */
    protected $guard;

    /**
     * TwoFactorAuthMiddleware constructor.
     * @param VerificationService $verificationService
     * @param Guard $guard
     */
    public function __construct(VerificationService $verificationService, Guard $guard) {
        $this->verificationService = $verificationService;
        $this->guard               = $guard;
    }
    
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next) {
        if( ! $this->verificationService->mustBeVerified($request->route(), $this->guard)) {
            return $next($request);
        }

        if ($request->ajax()) {
            return response('Unauthorized.', 401);
        }

        return redirect()->to( $this->verificationService->getPathToVerificationAction() );
    }
    
}
