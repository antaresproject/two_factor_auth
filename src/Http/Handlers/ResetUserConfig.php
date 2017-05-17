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

namespace Antares\Modules\TwoFactorAuth\Http\Handlers;

use Antares\Model\User;
use Antares\Modules\TwoFactorAuth\Services\UserProviderConfigService;
use Antares\Html\Form\FormBuilder;
use Antares\Html\Form\Fieldset;
use Antares\Html\Form\Grid as FormGrid;
use Illuminate\Container\Container as App;
use HTML;

class ResetUserConfig
{

    /**
     * Application instance.
     *
     * @var App
     */
    protected $app;

    /**
     * User provider config service instance.
     *
     * @var UserProviderConfigService
     */
    protected $userProviderConfigService;

    /**
     * ResetUserConfig constructor.
     * @param App $app
     * @param UserProviderConfigService $userProviderConfigService
     */
    public function __construct(App $app, UserProviderConfigService $userProviderConfigService)
    {
        $this->app                       = $app;
        $this->userProviderConfigService = $userProviderConfigService;
    }

    /**
     * Extends the form.
     *
     * @param User $user
     * @param FormBuilder $form
     */
    public function handle(User $user, FormBuilder $form)
    {
        if ($this->isAuthorize() AND $this->isConfigured($user)) {
            $this->extendForm($user, $form);
        }
    }

    /**
     * Check if logged admin can handle action based on ACL.
     * 
     * @return bool
     */
    protected function isAuthorize()
    {
        return $this->app->make('antares.acl')->make('antares/two_factor_auth')->can('reset-user-settings');
    }

    /**
     * Check if a user has already configured provider.
     * 
     * @param User $user
     * @return bool
     */
    protected function isConfigured(User $user)
    {
        return $this->userProviderConfigService->setUser($user)->hasAnyConfiguredAreas();
    }

    /**
     * Add a link to reset configuration inside the user edit form.
     * 
     * @param User $user
     * @param FormBuilder $form
     */
    protected function extendForm(User $user, FormBuilder $form)
    {
        $link = $this->getLink($user);

        $form->extend(function(FormGrid $form) use($link) {
            $fieldsetName = trans('antares/two_factor_auth::configuration.index');

            $form->fieldset($fieldsetName, function(Fieldset $fieldset) use($link) {
                $fieldset->control('input:text', '', function($control) use($link) {
                    $control->field(function () use($link) {
                        return $link;
                    });
                });
            });
        });
    }

    /**
     * Return a link for given user which allows to reset configuration.
     * 
     * @param User $user
     * @return string
     */
    protected function getLink(User $user)
    {
        $url   = handles('two_factor_auth.user.reset', ['id' => $user->id]);
        $title = trans('antares/two_factor_auth::users.reset-settings');
        $attrs = [
            'class'            => 'triggerable confirm',
            'data-title'       => trans('antares/two_factor_auth::users.prompts.title'),
            'data-description' => trans('antares/two_factor_auth::users.prompts.reset'),
            'data-icon'        => 'minus',
        ];

        return HTML::link($url, $title, $attrs);
    }

}
