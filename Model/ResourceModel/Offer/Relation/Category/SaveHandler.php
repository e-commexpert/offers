<?php
declare(strict_types=1);

namespace Dnd\Offers\Model\ResourceModel\Offer\Relation\Category;

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

        $oldCategories = $this->resourceOffer->lookupCategoryIds((int)$entity->getId());
        $newCategories = (array)$entity->getCategories();

        $table = $this->resourceOffer->getTable('dnd_offers_category');

        $delete = array_diff($oldCategories, $newCategories);
        if ($delete) {
            $where = [
                $linkField . ' = ?' => (int)$entity->getData($linkField),
                'category_id IN (?)' => $delete,
            ];
            $connection->delete($table, $where);
        }

        $insert = array_diff($newCategories, $oldCategories);
        if ($insert) {
            $data = [];
            foreach ($insert as $categoryId) {
                $data[] = [
                    $linkField => (int)$entity->getData($linkField),
                    'category_id' => (int)$categoryId,
                ];
            }
            $connection->insertMultiple($table, $data);
        }

        return $entity;
    }
}
