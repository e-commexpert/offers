<?php
declare(strict_types=1);

namespace Dnd\Offers\Block;

use Dnd\Offers\Model\ResourceModel\Offer\CollectionFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;

class Offer extends Template
{
    /**
     * @var Registry|null
     */
    private $coreRegistry = null;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Offer constructor.
     * @param Context $context
     * @param Registry $registry
     * @param CollectionFactory $collection
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CollectionFactory $collection,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $registry;
        $this->collectionFactory = $collection;
        $this->storeManager = $storeManager;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOffers()
    {
        $collection = $this->collectionFactory->create();
        $collection->addStoreFilter((int)$this->_storeManager->getStore()->getId());
        $collection->addCategoryFilter((int)$this->getCurrentCategory()->getId());
        $collection->addDateRangeFilter();
        return $collection;
    }

    /**
     * @return array|mixed|null
     */
    public function getCurrentCategory()
    {
        if (!$this->hasData('current_category')) {
            $this->setData(
                'current_category',
                $this->coreRegistry->registry('current_category')
            );
        }
        return $this->getData('current_category');
    }
}
