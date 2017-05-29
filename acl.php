<?php

use Antares\Acl\RoleActionList;
use Antares\Model\Role;
use Antares\Acl\Action;

$actions = [
    new Action('two_factor_auth.configuration.*', 'Configuration'),
    new Action('two_factor_auth.user.reset', 'Reset User Settings'),
];

$permissions = new RoleActionList;
$permissions->add(Role::admin()->name, $actions);

return $permissions;
