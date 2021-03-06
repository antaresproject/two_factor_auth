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

namespace Antares\Modules\TwoFactorAuth\Providers;

use Antares\Modules\TwoFactorAuth\Contracts\ProviderGatewayContract;
use Antares\Modules\TwoFactorAuth\Model\Provider;
use Antares\Contracts\Html\Form\Fieldset;
use function asset;

abstract class ProviderGateway implements ProviderGatewayContract
{

    /**
     * {@inheritdoc}
     */
    public function getIconUrl()
    {
        return asset('/public/packages/antares/two_factor_auth/img/' . $this->getIconName());
    }

    /**
     * Returns a field name for a giver area.
     *
     * @param string $areaId
     * @param string $name
     * @return string
     */
    protected function getAreaField($areaId, $name)
    {
        return sprintf('2fa[%s][settings][%s]', $areaId, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function setupBackendFormFieldset(Provider $provider, Fieldset $fieldset)
    {
        
    }

}
