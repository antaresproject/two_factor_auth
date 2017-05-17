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

namespace Antares\Modules\TwoFactorAuth\Validators;

use Antares\Support\Validator;

class ProviderValidator extends Validator
{

    /**
     * List of events.
     *
     * @var array
     */
    protected $events = [
        'antares.validate: provider',
    ];

    /**
     * Returns the validation rules.
     *
     * @return array
     */
    public function getValidationRules()
    {
        $rules = [];

        foreach ($this->rules as $name => $rule) {
            $ruleName         = sprintf('2fa.*.settings.%s', $name);
            $rules[$ruleName] = $rule;
        }

        return $rules;
    }

}
