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

namespace Antares\Modules\TwoFactorAuth\Http\Handlers;

use Antares\Model\User;
use Antares\Modules\TwoFactorAuth\Services\TwoFactorProvidersService;
use Antares\Modules\TwoFactorAuth\Http\Presenters\UserConfigurationPresenter;
use Antares\Html\Form\FormBuilder;
use Antares\Html\Form\Fieldset;
use Antares\Html\Form\Grid as FormGrid;

class UserConfig
{

    /**
     * 2FA providers service instance.
     *
     * @var TwoFactorProvidersService
     */
    protected $twoFactorProviderService;

    /**
     * User configuration presentert instance.
     *
     * @var UserConfigurationPresenter
     */
    protected $usersPresenter;

    /**
     * UserConfig constructor.
     * @param TwoFactorProvidersService $twoFactorProviderService
     * @param UserConfigurationPresenter $usersPresenter
     */
    public function __construct(TwoFactorProvidersService $twoFactorProviderService, UserConfigurationPresenter $usersPresenter)
    {
        $this->twoFactorProviderService = $twoFactorProviderService;
        $this->usersPresenter           = $usersPresenter;
    }

    /**
     * Extends the form.
     *
     * @param User $user
     * @param FormBuilder $form
     */
    public function handle(User $user, FormBuilder $form)
    {
        $this->extendForm($user, $form);
    }

    /**
     * Add a link to reset configuration inside the user edit form.
     *
     * @param User $user
     * @param FormBuilder $form
     */
    protected function extendForm(User $user, FormBuilder $form)
    {
        $areaProviders = $this->twoFactorProviderService->getAreaProvidersCollection();
        $items         = $this->usersPresenter->index($user, $areaProviders);

        if (empty($items)) {
            return;
        }

        $form->extend(function(FormGrid $form) use($items) {
            $fieldsetName = trans('antares/two_factor_auth::configuration.index');

            $form->findFieldsetOrCreateNew($fieldsetName, function(Fieldset $fieldset) use($items) {

                foreach ($items as $item) {
                    $fieldset->control('input:text', '', function($control) use($item) {
                        $control->field(function() use($item) {
                            return array_get($item, 'line');
                        });
                    })->label($item['area']->getLabel());
                }
            });
        });
    }

}
