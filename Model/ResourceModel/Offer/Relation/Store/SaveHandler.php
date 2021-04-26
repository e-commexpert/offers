<?php
declare(strict_types=1);

namespace Dnd\Offers\Model\ResourceModel\Offer\Relation\Store;

use Dnd\Offers\Api\Data\OfferInterface;
use Dnd\Offers\Model\ResourceModel\Offer;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Exception\LocalizedException;

class SaveHandler implements ExtensionInterface
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var Offer
     */
    private $resourceOffer;

    /**
     * SaveHandler constructor.
     *
     * @param MetadataPool $metadataPool
     * @param Offer $resourceOffer
     */
    public function __construct(
        MetadataPool $metadataPool,
        Offer $resourceOffer
    ) {
        $this->metadataPool = $metadataPool;
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
        $entityMetadata = $this->metadataPool->getMetadata(OfferInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $connection = $entityMetadata->getEntityConnection();

        $oldStores = $this->resourceOffer->lookupStoreIds((int)$entity->getId());
        $newStores = (array)$entity->getStores();

        $table = $this->resourceOffer->getTable('dnd_offers_store');

        $delete = array_diff($oldStores, $newStores);
        if ($delete) {
            $where = [
                $linkField . ' = ?' => (int)$entity->getData($linkField),
                'store_id IN (?)' => $delete,
            ];
            $connection->delete($table, $where);
        }

        $insert = array_diff($newStores, $oldStores);
        if ($insert) {
            $data = [];
            foreach ($insert as $storeId) {
                $data[] = [
                    $linkField => (int)$entity->getData($linkField),
                    'store_id' => (int)$storeId,
                ];
            }
            $connection->insertMultiple($table, $data);
        }

        return $entity;
    }
}
