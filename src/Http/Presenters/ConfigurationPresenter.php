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






namespace Antares\TwoFactorAuth\Http\Presenters;

use Antares\TwoFactorAuth\Contracts\ConfigurationPresenter as PresenterContract;
use Antares\TwoFactorAuth\Http\Breadcrumb\Breadcrumb;
use Antares\Contracts\Html\Form\Builder;
use Antares\Contracts\Html\Form\Factory as FormFactory;
use Antares\Contracts\Html\Form\Fieldset;
use Antares\Contracts\Html\Form\Grid as FormGrid;
use Antares\Html\Form\FormBuilder;
use Illuminate\View\View;
use Illuminate\Support\Collection;
use Antares\TwoFactorAuth\Services\TwoFactorProvidersService;
use Antares\TwoFactorAuth\Model\Provider;
use Antares\TwoFactorAuth\Facades\AreaProviders;
use Antares\Area\Contracts\AreaContract;
use function trans;

class ConfigurationPresenter implements PresenterContract
{

    /**
     * Datatables builder instance.
     *
     * @var Builder 
     */
    protected $htmlBuilder;

    /**
     * Breadcrumbs instance.
     *
     * @var Breadcrumb
     */
    protected $breadcrumb;

    /**
     * Form factory instance.
     *
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * 2FA providers service instance.
     *
     * @var TwoFactorProvidersService
     */
    protected $twoFactorProvidersService;

    /**
     * ConfigurationPresenter constructor.
     * @param FormFactory $formFactory
     * @param Breadcrumb $breadcrumb
     * @param TwoFactorProvidersService $twoFactorProvidersService
     */
    public function __construct(FormFactory $formFactory, Breadcrumb $breadcrumb, TwoFactorProvidersService $twoFactorProvidersService)
    {
        $this->formFactory               = $formFactory;
        $this->breadcrumb                = $breadcrumb;
        $this->twoFactorProvidersService = $twoFactorProvidersService->bind();
    }

    /**
     * Get the evaluated view contents for the given view.
     *
     * @param  string  $view
     * @param  array   $data
     * @param  array   $mergeData
     *
     * @return View
     */
    public function view($view, $data = [], $mergeData = [])
    {
        return view('antares/two_factor_auth::admin.configuration.' . $view, $data, $mergeData);
    }

    /**
     * 
     * {@inheritdoc}
     */
    public function index(Collection $areaProvidersCollection, FormBuilder $form)
    {
        //$this->breadcrumb->onIndex();

        /* @var $form FormGrid */
        $form->extend(function(FormGrid $form) use($areaProvidersCollection) {
            $fieldsetName = trans('antares/two_factor_auth::configuration.fieldset');
            $form->fieldset(function(Fieldset $fieldset) {
                $fieldset->legend(trans('antares/two_factor_auth::configuration.two_factor_auth'));
            });
            $form->findFieldsetOrCreateNew($fieldsetName, function (Fieldset $fieldset) use ($form, $areaProvidersCollection) {

                /* @var $areaProviders AreaProviders */

                foreach ($areaProvidersCollection as $areaProviders) {
                    $this->setupAreaFieldset($form, $areaProviders);
                }
            });
        });
        return $this->view('index', compact('form'));
    }

    /**
     * 
     * {@inheritdoc}
     */
    public function form(Provider $provider)
    {
        /* @var $form FormGrid */

        $providerGateway = $this->twoFactorProvidersService->getProviderGatewayByName($provider->getName());
        $area            = $this->twoFactorProvidersService->getAreaManager()->getById($provider->getAreaId());

        return $this->formFactory->of('antares.two_factor_auth.provider.settings', function (FormGrid $form) use($area, $provider, $providerGateway) {
                    $url = handles('two_factor_auth.configuration.update', compact('area'));

                    $form->simple($url);
                    $form->layout('antares/two_factor_auth::admin.configuration.provider');
                    $form->name('Two Factor Authentication Settings Form');

                    $form->hidden($this->getAreaField($area, 'enabled'), function($field) {
                        $field->value = 1;
                    });
                    $form->hidden($this->getAreaField($area, 'forced'), function($field) {
                        $field->value = 0;
                    });
                    $form->hidden($this->getAreaField($area, 'area'), function($field) use($provider) {
                        $field->value = $provider->getAreaId();
                    });
                    $form->hidden($this->getAreaField($area, 'name'), function($field) use($provider) {
                        $field->value = $provider->getName();
                    });
                    $form->hidden($this->getAreaField($area, 'id'), function($field) use($provider) {
                        $field->value = $provider->getId();
                    });

                    $title = trans('antares/two_factor_auth::configuration.fieldset');

                    $form->fieldset($title, function(Fieldset $fieldset) use($area, $provider, $providerGateway) {
                        $fieldset->control('input:checkbox', $this->getAreaField($area, 'forced'))
                                ->attributes($provider->isForced() ? ['checked'] : [])
                                ->value(1)
                                ->label(trans('antares/two_factor_auth::configuration.force'))
                                ->help(trans('antares/two_factor_auth::configuration.force_description'));

                        $providerGateway->setupBackendFormFieldset($provider, $fieldset);
                    });

                    $form->ajaxable()->rules($providerGateway->getValidator()->getValidationRules());
                });
    }

    /**
     * Returns field name.
     *
     * @param AreaContract $area
     * @param string $name
     * @return string
     */
    protected function getAreaField(AreaContract $area, $name)
    {
        return sprintf('2fa[%s][%s]', $area->getId(), $name);
    }

    /**
     * Populate dropdown with providers for each available areas.
     * 
     * @param FormGrid $form
     * @param AreaProviders $areaProviders
     */
    protected function setupAreaFieldset(FormGrid $form, AreaProviders $areaProviders)
    {
        $selected    = 0;
        $enabled     = $areaProviders->getEnabledModel();
        $area        = $areaProviders->getArea();
        $options     = ['0' => trans('Disabled')];
        $optionsData = [];
        $attributes  = [
            'class'         => 'two-factor-auth-area-provider-select',
            'data-selectar' => false
        ];
        foreach ($areaProviders->getModels() as $provider) {
            $provideGateway    = $provider->getProviderGateway();
            $editUrl           = handles('two_factor_auth.configuration.edit', compact('area', 'provider'));
            $options[$editUrl] = $provideGateway->getLabel();

            $optionsData[$editUrl] = ['icon-url' => $provideGateway->getIconUrl()];

            if ($enabled AND $enabled->isEquals($provider)) {
                $selected = $editUrl;
            }
        }

        $form->hidden($this->getAreaField($area, 'enabled'), function($field) {
            $field->value = 0;
        });

        $form->fieldset($area->getLabel(), function(Fieldset $fieldset) use($area, $attributes, $options, $selected, $optionsData) {
            $fieldset->layout('antares/two_factor_auth::admin.configuration.fieldset');

            $fieldset->control('select', $area->getId() . '-area-provider')
                    ->label($area->getLabel() . ' ' . trans('antares/two_factor_auth::configuration.provider'))
                    ->attributes($attributes)
                    ->options($options)
                    ->optionsData($optionsData)
                    ->value($selected)
                    ->wrapper(['class' => 'w400']);
        });
    }

}
