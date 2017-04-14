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

namespace Antares\TwoFactorAuth\Services;

use Illuminate\Session\Store as Session;

class AuthStore
{

    /**
     * Session instance.
     * @var Session
     */
    protected $session;

    /**
     * Session key name.
     *
     * @var string
     */
    protected static $name = '2fa.auth';

    /**
     * AuthStore constructor.
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Check if an authentication is verified.
     * 
     * @return boolean
     */
    public function isVerified()
    {
        return (bool) $this->session->get(self::$name, false);
    }

    /**
     * Verify an authentication.
     */
    public function verify()
    {
        $this->session->put(self::$name, true);
    }

    /**
     * Unverify an authentication.
     */
    public function unverify()
    {
        $this->session->forget(self::$name);
    }

}
