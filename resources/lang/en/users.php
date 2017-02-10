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
    'reset-settings'                         => 'Reset Settings',
    'prompts'                                => [
        'title' => 'Resetting Two-Factor Authentication settings',
        'reset' => 'Do you really want to reset Two Factor Authentication settings for the selected user?',
    ],
    'responses'                              => [
        'reset' => [
            'success' => 'Two Factor Authentication settings has been successfully reset for the selected user.',
            'fail'    => 'An error occurs while reset Two Factor Authentication settings for the selected user.',
        ],
    ],
    'reset_two_factor_auth_user_description' => 'Reset two factor auth configuration for user :fullname',
    'reset_two_factor_auth'                  => 'Reset two factor auth'
];
