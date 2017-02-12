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






namespace Antares\TwoFactorAuth\Http\Controllers\Admin;

use Antares\TwoFactorAuth\Processor\ConfigurationProcessor;
use Antares\TwoFactorAuth\Contracts\ConfigurationListener;
use Antares\Foundation\Http\Controllers\AdminController;
use Antares\Area\Contracts\AreaContract;
use Antares\TwoFactorAuth\Model\Provider;
use Antares\Contracts\Html\Builder;
use Illuminate\Http\Request;

class ConfigurationController extends AdminController implements ConfigurationListener
{

    /**
     * Request instance.
     *
     * @var Request
     */
    protected $request;

    /**
     * ConfigurationController constructor.
     * @param ConfigurationProcessor $processor
     * @param Request $request
     */
    public function __construct(ConfigurationProcessor $processor, Request $request)
    {
        parent::__construct();

        $this->processor = $processor;
        $this->request   = $request;
    }

    /**
     * Setup middleware based on ACL.
     */
    public function setupMiddleware()
    {
        
    }

    /**
     * Show configuration page of providers.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return $this->processor->index();
    }

    /**
     * Request for edit provider on selected area.
     *
     * @param AreaContract $area
     * @param Provider $provider
     * @return mixed
     */
    public function edit(AreaContract $area, Provider $provider)
    {
        return $this->processor->edit($this, $area, $provider);
    }

    /**
     * Request for update provider on selected area.
     *
     * @param Request $request
     * @return mixed
     */
    public function update(Request $request)
    {
        $data = array_get($request->input(), '2fa', []);
        return $this->processor->update($this, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function showProviderConfiguration(AreaContract $area, Provider $provider, Builder $form)
    {
        if ($this->request->ajax()) {
            return $form->render();
        }

        return response('Method not allowed', 405);
    }

    /**
     * {@inheritdoc}
     */
    public function updateFailed($msg)
    {
        app('antares.messages')->add('error', $msg);
        return redirect()->back();
    }

    /**
     * {@inheritdoc}
     */
    public function updateSuccess($msg)
    {
        app('antares.messages')->add('success', $msg);
        return redirect()->to(area() . '/two_factor_auth');
    }

}