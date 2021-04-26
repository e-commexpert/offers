<?php
declare(strict_types=1);

namespace Dnd\Offers\Model\Offer;

use Dnd\Offers\Api\Data\OfferInterface;
use Dnd\Offers\Model\Offer;
use Dnd\Offers\Model\OfferFactory;
use Dnd\Offers\Model\ResourceModel\Offer\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Store\Model\Store;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var array
     */
    private $loadedData;

    /**
     * @var
     */
    private $fileInfo;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var string
     */
    private $requestScopeFieldName = 'store';

    /**
     * @var OfferFactory
     */
    private $offerFactory;

    /**
     * DataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param Registry $registry
     * @param RequestInterface $request
     * @param OfferFactory $offerFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        Registry $registry,
        RequestInterface $request,
        OfferFactory $offerFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->registry = $registry;
        $this->request = $request;
        $this->offerFactory = $offerFactory;
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
    }

    /**
     * @return array
     * @throws FileSystemException
     * @throws NoSuchEntityException
     */
    public function getData(): ?array
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $offer = $this->getCurrentOffer();
        if ($offer) {
            $offerData = $offer->getData();
            $offerData = $this->convertValues($offer, $offerData);

            $this->loadedData[$offer->getId()] = $offerData;
        }
        return $this->loadedData;
    }

    /**
     * @return mixed|null
     * @throws NoSuchEntityException
     */
    public function getCurrentOffer()
    {
        $category = $this->registry->registry(Offer::ENTITY_REGISTRY_NAME);
        if ($category) {
            return $category;
        }
        $requestId = $this->request->getParam($this->requestFieldName);
        $requestScope = $this->request->getParam(
            $this->requestScopeFieldName,
            Store::DEFAULT_STORE_ID
        );
        if ($requestId) {
            $category = $this->offerFactory->create();
            $category->setStoreId($requestScope);
            $category->load($requestId);
            if (!$category->getId()) {
                throw NoSuchEntityException::singleField('id', $requestId);
            }
        }
        return $category;
    }

    /**
     * @param $offer
     * @param $offerData
     * @return mixed
     * @throws FileSystemException
     */
    private function convertValues(
        OfferInterface $offer,
        array $offerData
    ) {
        $attributeCode = 'image';
        if (!isset($offerData[$attributeCode])) {
            return $offerData;
        }

        unset($offerData[$attributeCode]);

        $fileName = $offer->getData($attributeCode);
        $fileInfo = $this->getFileInfo();

        if ($fileInfo->isExist($fileName)) {
            $stat = $fileInfo->getStat($fileName);
            $mime = $fileInfo->getMimeType($fileName);

            $offerData[$attributeCode][0]['name'] = basename($fileName);

            if ($fileInfo->isBeginsWithMediaDirectoryPath($fileName)) {
                $offerData[$attributeCode][0]['url'] = $fileName;
            }
            else {
                $offerData[$attributeCode][0]['url'] = $offer->getImageUrl($attributeCode);
            }

            $offerData[$attributeCode][0]['size'] = isset($stat) ? $stat['size'] : 0;
            $offerData[$attributeCode][0]['type'] = $mime;
        }

        return $offerData;
    }

    /**
     * @return mixed
     */
    private function getFileInfo()
    {
        if ($this->fileInfo === null) {
            $this->fileInfo = ObjectManager::getInstance()
                ->get(FileInfo::class);
        }
        return $this->fileInfo;
    }
}
