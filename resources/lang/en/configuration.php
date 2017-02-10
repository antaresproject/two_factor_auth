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






return [
    'provider'          => 'Provider',
    'index'             => 'Two-Factor Authentication',
    'edit'              => ':provider Configuration for :area',
    'fieldset'          => 'Provider Configuration',
    'backend-no-config' => 'This provider does not support the setup configuration.',
    'force_description' => 'Force users to configure Two-Factor Authentication after they log in',
    'force'             => 'Force',
    'activated'         => 'Activated',
    'two_factor_auth'   => 'Two Factor Auth',
    'enable'            => [
        'label'  => 'Enable',
        'title'  => 'Enabling Two-Factor Authentication',
        'prompt' => 'Do you want to enable the Two-Factor Authentication for the chosen area?'
    ],
    'disable'           => [
        'label'  => 'Disable',
        'title'  => 'Disabling Two-Factor Authentication',
        'prompt' => 'Do you want to disable the Two-Factor Authentication for the chosen area?'
    ],
    'responses'         => [
        'enable'  => [
            'success' => 'Two-Factor Authentication for the :area has been successfully enabled.',
            'fail'    => 'Error occurred when enabling the provider.'
        ],
        'disable' => [
            'success' => 'Two-Factor Authentication for the :area has been successfully disabled.',
            'fail'    => 'Error occurred when disabling the provider.',
        ],
        'update'  => [
            'success' => 'Two-Factor Authentication has been successfully updated.',
            'fail'    => 'Error occurred when updating the provider.',
        ],
    ],
];
