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






namespace Antares\TwoFactorAuth\Collection;

use Illuminate\Support\Collection as BaseCollection;

class ProvidersCollection extends BaseCollection {
    
    /**
     * Returns providers models which belong to given area.
     * 
     * @param string $area
     * @return ProvidersCollection
     */
    public function filterByArea($area) {
        return $this->filter(function($provider) use($area) {
            return $provider->area === $area;
        });
    }
    
    /**
     * Returns providers models based on a name.
     * 
     * @param string $name
     * @return ProvidersCollection
     */
    public function filterByGatewayName($name) {
        return $this->filter(function($provider) use($name) {
            return $provider->name === $name;
        });
    }
    
}
