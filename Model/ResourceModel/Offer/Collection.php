<?php
declare(strict_types=1);

namespace Dnd\Offers\Model\ResourceModel\Offer;

use DateTime;
use Dnd\Offers\Api\Data\OfferInterface;
use Dnd\Offers\Model\ResourceModel\Offer;
use Exception;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Collection extends AbstractCollection
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'offers_collection';
    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'offers_collection';
    /**
     * @var string
     */
    protected $_idFieldName = 'offer_id';
    /**
     * @var MetadataPool
     */
    private $metadataPool;
    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Collection constructor.
     *
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param MetadataPool $metadataPool
     * @param StoreManagerInterface $storeManager
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        MetadataPool $metadataPool,
        StoreManagerInterface $storeManager,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->metadataPool = $metadataPool;
        $this->storeManager = $storeManager;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }

    /**
     * @param int $storeId
     * @return $this
     */
    public function addStoreFilter(
        int $storeId
    ): collection {
        $store[] = $storeId;
        $store[] = Store::DEFAULT_STORE_ID;

        $this->getSelect()->join(
            ['offers_store' => $this->getTable('dnd_offers_store')],
            'main_table.offer_id = offers_store.offer_id',
            []
        );

        $this->getSelect()->where('offers_store.store_id IN (?)', $store);

        return $this;
    }

    /**
     * @param int $categoryId
     * @return $this
     */
    public function addCategoryFilter(
        int $categoryId
    ): collection {
        $this->getSelect()->join(
            ['offers_category' => $this->getTable('dnd_offers_category')],
            'main_table.offer_id = offers_category.offer_id',
            []
        );

        $this->getSelect()
            ->where('offers_category.category_id IN (?)', $categoryId);

        return $this;
    }

    public function addDateRangeFilter()
    {
        $now = new DateTime();

        $this
            ->addFieldToFilter(
                'date_from',
                ['lteq' => $now->format('Y-m-d')]
            )
            ->addFieldToFilter(
                'date_to',
                ['gteq' => $now->format('Y-m-d')]
            );
    }

    protected function _construct()
    {
        $this->_init(
            \Dnd\Offers\Model\Offer::class,
            Offer::class
        );
        $this->_map['fields']['store'] = 'store_table.store_id';
        $this->_map['fields']['offer_id'] = 'main_table.offer_id';
    }

    /**
     * @return Collection
     * @throws Exception
     */
    protected function _afterLoad(): collection
    {
        $entityMetadata = $this->metadataPool->getMetadata(OfferInterface::class);

        $this->performStoreAfterLoad(
            'dnd_offers_store',
            $entityMetadata->getLinkField()
        );
        $this->performCategoriesAfterLoad(
            'dnd_offers_category',
            $entityMetadata->getLinkField()
        );

        return parent::_afterLoad();
    }

    /**
     * @param string $tableName
     * @param string $linkField
     * @throws NoSuchEntityException
     */
    protected function performStoreAfterLoad(
        string $tableName,
        string $linkField
    ) {
        $linkedIds = $this->getColumnValues($linkField);
        if (count($linkedIds)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from(['offers_store' => $this->getTable($tableName)])
                ->where('offers_store.' . $linkField . ' IN (?)', $linkedIds);
            $result = $connection->fetchAll($select);
            if ($result) {
                $storesData = [];
                foreach ($result as $storeData) {
                    $storesData[$storeData[$linkField]][] = $storeData['store_id'];
                }

                foreach ($this as $item) {
                    $linkedId = $item->getData($linkField);
                    if (!isset($storesData[$linkedId])) {
                        continue;
                    }
                    $storeIdKey = array_search(
                        Store::DEFAULT_STORE_ID,
                        $storesData[$linkedId],
                        true
                    );
                    if ($storeIdKey !== false) {
                        $stores = $this->storeManager->getStores(false, true);
                        $storeId = current($stores)->getId();
                        $storeCode = key($stores);
                    } else {
                        $storeId = current($storesData[$linkedId]);
                        $storeCode = $this->storeManager->getStore($storeId)
                            ->getCode();
                    }
                    $item->setData('_first_store_id', $storeId);
                    $item->setData('store_code', $storeCode);
                    $item->setData('store_id', $storesData[$linkedId]);
                }
            }
        }
    }

    /**
     * @param string $tableName
     * @param string $linkField
     */
    protected function performCategoriesAfterLoad(
        string $tableName,
        string $linkField
    ) {
        $linkedIds = $this->getColumnValues($linkField);
        if (count($linkedIds)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from(['offers_category' => $this->getTable($tableName)])
                ->where('offers_category.' . $linkField . ' IN (?)', $linkedIds);
            $result = $connection->fetchAll($select);

            if ($result) {
                $categoriesData = [];
                foreach ($result as $categoryData) {
                    $categoriesData[$categoryData[$linkField]][] = $categoryData['category_id'];
                }
                foreach ($this as $item) {
                    $linkedId = $item->getData($linkField);
                    if (!isset($categoriesData[$linkedId])) {
                        continue;
                    }
                    $item->setData('categories', $categoriesData[$linkedId]);
                }
            }
        }
    }
}
