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

namespace Antares\Modules\TwoFactorAuth\Http\Presenters;

use Antares\Modules\TwoFactorAuth\Model\Provider;
use Antares\Contracts\Html\Form\Factory as FormFactory;
use Antares\Contracts\Html\Form\Fieldset;
use Antares\Contracts\Html\Form\Grid as FormGrid;
use Antares\Modules\TwoFactorAuth\Model\UserConfig;
use Antares\Area\Contracts\AreaContract;

class AuthPresenter
{

    /**
     * Form factory instance.
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * AuthPresenter constructor.
     * @param FormFactory $formFactory
     */
    public function __construct(FormFactory $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * Returns a form for a Two-Factor Auth login page.
     *
     * @param UserConfig $user
     * @param AreaContract $area
     * @param Provider $provider
     * @param String $secretKey
     * @return \Antares\Contracts\Html\Builder
     */
    public function verify(UserConfig $user, AreaContract $area, Provider $provider, $secretKey = null)
    {
        return $this->formFactory->of('antares.two_factor_auth.provider.auth.verify', function (FormGrid $form) use ($user, $area, $provider, $secretKey) {
                    $form->simple(handles('two_factor_auth.post.verify', compact('area')));
                    $form->name('Two-Factor Authentication User Verification Form');
                    $form->layout('antares/two_factor_auth::admin.auth.partials._form');

                    $form->hidden('provider_id', function ($field) use ($provider) {
                        $field->value = $provider->getId();
                    });
                    $form->hidden('user_id', function ($field) use ($user) {
                        $field->value = $user->getId();
                    });
                    $form->hidden('secret_key', function ($field) use ($secretKey) {
                        $field->value = $secretKey;
                    });

                    $title = trans('antares/two_factor_auth::auth.verify');

                    $form->fieldset($title, function (Fieldset $fieldset) use ($user, $provider) {
                        $provider->getProviderGateway()->setupVerifyFormFieldset($fieldset, $user);

                        $fieldset->control('button', 'button')
                                ->attributes(['type' => 'submit', 'class' => 'btn btn-primary'])
                                ->value(trans('Submit & Save'));
                        if (!$user->configured) {
                            $fieldset->control('button', 'cancel')
                                    ->field(function() {
                                        return app('html')->link(handles('two_factor_auth.get.configuration', ['area' => area()]), trans('Go Back'), ['class' => 'btn btn--md btn--default mdl-button mdl-js-button']);
                                    });
                        }
                    });
                });
    }

}
