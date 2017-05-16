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

namespace Antares\Modules\TwoFactorAuth\Http\Presenters;

use Antares\Modules\TwoFactorAuth\Facades\AreaProviders;
use HTML;
use Illuminate\Support\Collection;
use Antares\Modules\TwoFactorAuth\Services\UserProviderConfigService;
use Antares\Model\User;
use Antares\Modules\TwoFactorAuth\Model\UserConfig;
use Antares\Contracts\Html\Form\Factory as FormFactory;
use Antares\Contracts\Html\Form\Fieldset;
use Antares\Contracts\Html\Form\Grid as FormGrid;
use Antares\Area\Contracts\AreaContract;
use Antares\Modules\TwoFactorAuth\Model\Provider;

class UserConfigurationPresenter
{

    /**
     * User provider config service instance.
     *
     * @var UserProviderConfigService
     */
    protected $userProviderConfigService;

    /**
     * Form factory instance.
     *
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * UserConfigurationPresenter constructor.
     * @param UserProviderConfigService $userProviderConfigService
     * @param FormFactory $formFactory
     */
    public function __construct(UserProviderConfigService $userProviderConfigService, FormFactory $formFactory)
    {
        $this->userProviderConfigService = $userProviderConfigService;
        $this->formFactory               = $formFactory;
    }

    /**
     * Returns an array with supported areas and link.
     *
     * @param User $user
     * @param Collection $areaProvidersCollection
     * @return array
     */
    public function index(User $user, Collection $areaProvidersCollection)
    {
        $items = [];

        foreach ($areaProvidersCollection as $areaProviders) {
            /* @var $areaProviders AreaProviders */

            $enabled = $areaProviders->getEnabledModel();

            if (!$enabled instanceof Provider) {
                continue;
            }

            $area = $areaProviders->getArea();

            if ($enabled->isForced()) {
                $items[] = [
                    'area' => $area,
                    'line' => trans('antares/two_factor_auth::configuration.activated'),
                ];
            } elseif ($this->userProviderConfigService->hasEnabledArea($area)) {
                $url   = handles('two_factor_auth.user.configuration.disable', compact('area', 'user'));
                $attrs = [
                    'class'            => 'triggerable confirm',
                    'data-title'       => trans('antares/two_factor_auth::configuration.disable.title'),
                    'data-description' => trans('antares/two_factor_auth::configuration.disable.prompt'),
                    'data-icon'        => 'minus',
                ];

                $items[] = [
                    'area' => $area,
                    'line' => HTML::link($url, trans('antares/two_factor_auth::configuration.disable.label'), $attrs),
                ];
            } elseif (!$this->userProviderConfigService->hasEnabledArea($area)) {
                $url   = handles('two_factor_auth.user.configuration.enable', compact('area', 'user'));
                $attrs = [
                    'class'            => 'triggerable confirm',
                    'data-title'       => trans('antares/two_factor_auth::configuration.enable.title'),
                    'data-description' => trans('antares/two_factor_auth::configuration.enable.prompt'),
                    'data-icon'        => 'minus',
                ];

                $items[] = [
                    'area' => $area,
                    'line' => HTML::link($url, trans('antares/two_factor_auth::configuration.enable.label'), $attrs),
                ];
            }
        }

        return $items;
    }

    /**
     * Returns a form for a frontend page where Two-Factor Auth should be configured for first use.
     *
     * @param UserConfig $user
     * @param AreaContract $area
     * @param Provider $provider
     * @return \Antares\Contracts\Html\Builder
     */
    public function configure(UserConfig $user, AreaContract $area, Provider $provider)
    {
        return $this->formFactory->of('antares.two_factor_auth.provider.auth.configure', function (FormGrid $form) use($user, $area, $provider) {
                    $url = handles('two_factor_auth.user.post.configuration', compact('area'));

                    $form->simple($url);
                    $form->name('Two-Factor Authentication User Settings Form');
                    $form->layout('antares/two_factor_auth::admin.auth.partials._form');

                    $form->hidden('provider_id', function($field) use($provider) {
                        $field->value = $provider->getId();
                    });
                    $form->hidden('user_id', function($field) use($user) {
                        $field->value = $user->getId();
                    });

                    $title = trans('antares/two_factor_auth::auth.configuration');

                    $form->fieldset($title, function(Fieldset $fieldset) use($user, $provider) {
                        $provider->getProviderGateway()->setupFrontendFormFieldset($fieldset, $user);

                        $fieldset->control('button', 'button')
                                ->attributes(['type' => 'submit', 'class' => 'btn btn-primary'])
                                ->value(trans('Continue'));


                        $fieldset->control('button', 'cancel')
                                ->field(function() {
                                    return app('html')->link(handles('two_factor_auth.get.cancel', ['area' => area()]), trans('cancel'), ['class' => 'btn btn--md btn--default mdl-button mdl-js-button']);
                                });
                    });
                });
    }

}
