<?php
declare(strict_types=1);

namespace Dnd\Offers\Model\ResourceModel\Offer\Relation\Category;

use Dnd\Offers\Model\ResourceModel\Offer;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Exception\LocalizedException;

class ReadHandler implements ExtensionInterface
{
    /**
     * @var Offer
     */
    private $resourceOffer;

    /**
     * ReadHandler constructor.
     * @param Offer $resourceOffer
     */
    public function __construct(
        Offer $resourceOffer
    ) {
        $this->resourceOffer = $resourceOffer;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return bool|object
     * @throws LocalizedException
     */
    public function execute(
        $entity,
        $arguments = []
    ) {
        if ($entity->getId()) {
            $stores = $this->resourceOffer->lookupCategoryIds((int)$entity->getId());
            $entity->setData('categories', $stores);
        }
        return $entity;
    }
}
