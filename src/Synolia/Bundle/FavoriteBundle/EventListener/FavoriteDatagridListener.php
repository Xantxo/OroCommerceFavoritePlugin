<?php

declare(strict_types=1);

namespace Synolia\Bundle\FavoriteBundle\EventListener;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SearchBundle\Datagrid\Datasource\SearchDatasource;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Synolia\Bundle\FavoriteBundle\Entity\Favorite;
use Synolia\Bundle\FavoriteBundle\Entity\Repository\FavoriteRepository;

class FavoriteDatagridListener
{
    public function __construct(
        protected EntityManager $entityManager,
        protected AclHelper $aclHelper,
        protected TokenAccessorInterface $tokenAccessor,
    ) {
    }

    /**
     * @throws NotSupported
     */
    public function onBuildAfter(BuildAfter $event): void
    {
        $datasource = $event->getDatagrid()->getDatasource();
        if (!$datasource instanceof SearchDatasource) {
            return;
        }

        /** @var FavoriteRepository $repo */
        $repo = $this->entityManager->getRepository(Favorite::class);
        $user = $this->tokenAccessor->getUser();
        $organization = $this->tokenAccessor->getOrganization();
        $favProductIds = [];
        if ($user instanceof CustomerUser and $organization instanceof Organization) {
            /** @var FavoriteRepository $favoriteRepo */
            $favoriteRepo = $this->entityManager->getRepository(Favorite::class);
            $favProductIds = $favoriteRepo->findAllProductIdsFilteredByAcl($this->aclHelper, $user, $organization);
        }

        if (empty($favProductIds)) {
            $favProductIds = [0];
        }

        $datasource
            ->getSearchQuery()
            ->getCriteria()->where(Criteria::expr()->in('integer.system_entity_id', $favProductIds));
    }
}
