<?php

declare(strict_types=1);

namespace Synolia\Bundle\FavoriteBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Synolia\Bundle\FavoriteBundle\Entity\Favorite;

class FavoriteRepository extends EntityRepository
{
    public function findAllFilteredByAcl(AclHelper $aclHelper, CustomerUser $user, Organization $organization): array
    {
        return $aclHelper->apply($this->getFavoritesProductsCollection($user, $organization))->getResult();
    }

    public function getFavoritesProductsCollection(CustomerUser $user, Organization $organization): Query
    {
        return $this->createQueryBuilder('f')
            ->resetDQLPart('select')
            ->addSelect('IDENTITY(f.product) as product_id')
            ->andWhere('f.customerUser = :user')
            ->andWhere('f.organization = :organization')
            ->setParameters([
                'user' => $user,
                'organization' => $organization,
            ])
            ->getQuery();
    }

    public function findAllProductIdsFilteredByAcl(AclHelper $aclHelper, CustomerUser $user, Organization $organization): array
    {
        $ids = [];
        $favorites = $this->findAllFilteredByAcl($aclHelper, $user, $organization);

        /** @var Favorite $favorite */
        foreach ($favorites as $favorite) {
            $ids[] = $favorite['product_id'];
        }

        return $ids;
    }
}
