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






namespace Antares\TwoFactorAuth\Facades;

use Antares\Area\Contracts\AreaContract;
use Antares\TwoFactorAuth\Model\Provider;

class AreaProviders {
    
    /**
     * Area instance.
     *
     * @var AreaContract
     */
    protected $area;
    
    /**
     * Array of Provider instances.
     *
     * @var Provider[]
     */
    protected $models = [];

    /**
     * AreaProviders constructor.
     * @param AreaContract $area
     */
    public function __construct(AreaContract $area) {
        $this->area = $area;
    }
    
    /**
     * Add a provider model.
     * 
     * @param Provider $model
     */
    public function addModel(Provider $model) {
        $this->models[] = $model;
    }
    
    /**
     * Return an area object.
     * 
     * @return AreaContract
     */
    public function getArea() {
        return $this->area;
    }
    
    /**
     * Get all providers models which belong to an area.
     * 
     * @return Provider[]
     */
    public function getModels() {
        return $this->models;
    }
    
    /**
     * Get provider model of an area which is enabled. Return NULL if not found.
     * 
     * @return Provider | null
     */
    public function getEnabledModel() {
        foreach($this->models as $model) {
            if($model->isEnabled()) {
                return $model;
            }
        }

        return null;
    }
    
}
