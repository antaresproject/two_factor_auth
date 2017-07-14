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

namespace Antares\Modules\TwoFactorAuth\Http\Controllers\Admin;

use Antares\Area\Model\Area;
use Antares\Modules\TwoFactorAuth\Processor\UserConfigurationProcessor;
use Antares\Modules\TwoFactorAuth\Processor\AuthProcessor;
use Antares\Modules\TwoFactorAuth\Contracts\AuthListener;
use Antares\Foundation\Http\Controllers\AdminController;
use Antares\Modules\TwoFactorAuth\Model\Provider;
use Illuminate\Support\Facades\Redirect;
use Antares\Area\Contracts\AreaContract;
use Antares\Contracts\Html\Builder;
use Antares\Area\AreaManager;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends AdminController implements AuthListener
{

    /**
     * Auth processor instance.
     *
     * @var AuthProcessor
     */
    protected $processor;

    /**
     * User configuration processor instance.
     *
     * @var UserConfigurationProcessor
     */
    protected $userConfigurationProcessor;

    /**
     * AuthController constructor.
     * @param AuthProcessor $authProcessor
     * @param UserConfigurationProcessor $userConfigurationProcessor
     */
    public function __construct(AuthProcessor $authProcessor, UserConfigurationProcessor $userConfigurationProcessor)
    {
        parent::__construct();

        $this->processor                  = $authProcessor;
        $this->userConfigurationProcessor = $userConfigurationProcessor;
    }

    /**
     * Setup middleware based on ACL.
     */
    protected function setupMiddleware()
    {
        $this->middleware('antares.auth');
    }

    /**
     * {@inheritdoc}
     */
    public function getVerify($area, $withError = false)
    {
        $area = app(AreaManager::class)->getById($area);

        if (!$this->userConfigurationProcessor->isConfigured($area)) {
            return redirect()->to(handles('two_factor_auth.get.configuration', compact('area')));
        }

        if ($withError) {
            $message = trans('antares/two_factor_auth::auth.wrong_credentials');
            app('antares.messages')->add('error', $message);
        }

        return $this->processor->verify($this, $area);
    }

    /**
     * Post verify request.
     * 
     * @param AreaContract $area
     * @param Request $request
     * @return View
     */
    public function postVerify($area, Request $request)
    {
        $area = app(AreaManager::class)->getById($area);
        return $this->processor->verifyCredentials($this, $area, $request->input());
    }

    public function verifyFailed()
    {
        return $this->redirectWithErrors(url()->previous(), new \Antares\Messages\MessageBag(['verification_code' => trans('antares/two_factor_auth::auth.invalid_verification_code')]));
    }

    /**
     * {@inheritdoc}
     */
    public function showVerifyForm(Provider $provider, Builder $form)
    {
        $title = $provider->getProviderGateway()->getLabel();

        set_meta('title', $title);

        return view('antares/two_factor_auth::admin.auth.verify', compact('form'));
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(Area $area)
    {
        if ($area->getId() === 'client') {
            return redirect()->to('/');
        }
        return redirect()->to(handles('antares/foundation::/'));
    }

    /**
     * Cancel two factor auth verification
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel()
    {
        auth()->logout();
        messages('success', trans('antares/foundation::response.credential.logged-out'));
        return Redirect::intended(handles('antares/foundation::login'));
    }

}
