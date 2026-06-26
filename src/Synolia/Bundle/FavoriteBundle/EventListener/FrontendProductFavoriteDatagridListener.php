<?php

declare(strict_types=1);

namespace Synolia\Bundle\FavoriteBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SearchBundle\Datagrid\Event\SearchResultAfter;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Synolia\Bundle\FavoriteBundle\Entity\Favorite;
use Synolia\Bundle\FavoriteBundle\Entity\Repository\FavoriteRepository;

class FrontendProductFavoriteDatagridListener
{
    public function __construct(
        private readonly EntityManager $entityManager,
        private readonly AclHelper $aclHelper,
        private readonly TokenAccessorInterface $tokenAccessor,
    ) {
    }

    public function onBuildBefore(BuildBefore $event): void
    {
        $config = $event->getConfig();

        $config->offsetAddToArrayByPath(
            '[properties]',
            [
                'favorite' => [
                    'type' => 'field',
                    'frontend_type' => PropertyInterface::TYPE_BOOLEAN,
                ],
            ],
        );
    }

    public function onResultAfter(SearchResultAfter $event): void
    {
        $records = $event->getRecords();
        if (0 === \count($records)) {
            return;
        }

        $user = $this->tokenAccessor->getUser();
        $organization = $this->tokenAccessor->getOrganization();
        $favProductIds = [];
        if ($user instanceof CustomerUser and $organization instanceof Organization) {
            /** @var FavoriteRepository $favoriteRepo */
            $favoriteRepo = $this->entityManager->getRepository(Favorite::class);
            $favProductIds = $favoriteRepo->findAllProductIdsFilteredByAcl($this->aclHelper, $user, $organization);
        }
        /** @var ResultRecord $record */
        foreach ($records as $record) {
            $productId = $record->getValue('id');
            $record->setValue('favorite', false);

            if (\in_array((int) $productId, $favProductIds, true)) {
                $record->setValue('favorite', true);
            }
        }
    }
}
