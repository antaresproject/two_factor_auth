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






namespace Antares\TwoFactorAuth\Http\Breadcrumb;

use Antares\Breadcrumb\Navigation;
use DaveJamesMiller\Breadcrumbs\Generator;

class Breadcrumb extends Navigation {

    /**
     * Name of the breadcrumb.
     *
     * @var string
     */
    protected static $name = 'two_factor_auth';

    /**
     * Register a breadcrumb on an admin index page.
     */
    public function onIndex() {
        $this->breadcrumbs->register(self::$name, function(Generator $breadcrumbs) {
            $breadcrumbs->push('General Configuration');
        });

        $this->shareOnView(self::$name);
    }

}
