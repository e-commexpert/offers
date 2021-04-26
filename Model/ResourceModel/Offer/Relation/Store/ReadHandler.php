<?php
declare(strict_types=1);

namespace Dnd\Offers\Model\ResourceModel\Offer\Relation\Store;

use Dnd\Offers\Model\ResourceModel\Offer;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

class ReadHandler implements ExtensionInterface
{
    /**
     * @var \Dnd\Offers\Model\ResourceModel\Offer
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(
        $entity,
        $arguments = []
    ) {
        if ($entity->getId()) {
            $stores = $this->resourceOffer->lookupStoreIds((int)$entity->getId());
            $entity->setData('store_id', $stores);
            $entity->setData('stores', $stores);
        }
        return $entity;
    }
}
