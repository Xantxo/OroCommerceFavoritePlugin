<?php

declare(strict_types=1);

namespace Synolia\Bundle\FavoriteBundle\Migrations\Data\ORM;

use Oro\Bundle\CustomerBundle\Migrations\Data\ORM\AbstractMassUpdateCustomerUserRolePermissions;
use Oro\Bundle\CustomerBundle\Migrations\Data\ORM\LoadCustomerUserRoles;
use Synolia\Bundle\FavoriteBundle\Entity\Favorite;

/**
 * Update Favorite default permissions for predefined roles.
 */
class UpdateFrontendPermissionsForRoles extends AbstractMassUpdateCustomerUserRolePermissions
{
    public function getDependencies(): array
    {
        return [LoadCustomerUserRoles::class];
    }

    protected function getACLData(): array
    {
        return [
            'ROLE_FRONTEND_ADMINISTRATOR' => [
                'entity:' . Favorite::class => ['VIEW_SYSTEM', 'CREATE_SYSTEM', 'EDIT_SYSTEM', 'DELETE_SYSTEM'],
            ],
            'ROLE_FRONTEND_BUYER' => [
                'entity:' . Favorite::class => ['VIEW_SYSTEM', 'CREATE_SYSTEM', 'EDIT_SYSTEM', 'DELETE_SYSTEM'],
            ],
            'ROLE_FRONTEND_ANONYMOUS' => [
                'entity:' . Favorite::class => ['VIEW_NONE', 'CREATE_NONE', 'EDIT_NONE', 'DELETE_NONE'],
            ],
        ];
    }
}
