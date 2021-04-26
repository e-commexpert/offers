<?php
declare(strict_types=1);

namespace Dnd\Offers\Model\ResourceModel;

use Dnd\Offers\Api\Data\OfferInterface;
use Dnd\Offers\Offer\OfferImageUpload;
use Exception;
use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Psr\Log\LoggerInterface;

class Offer extends AbstractDb
{
    const IMAGE = 'image';
    /**
     * @var MetadataPool
     */
    private $metadataPool;
    /**
     * @var EntityManager
     */
    private $entityManager;
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ImageUploader
     */
    private $imageUploader;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Offer constructor.
     *
     * @param Context $context
     * @param MetadataPool $metadataPool
     * @param EntityManager $entityManager
     * @param Filesystem $filesystem
     * @param LoggerInterface $logger
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        MetadataPool $metadataPool,
        EntityManager $entityManager,
        Filesystem $filesystem,
        LoggerInterface $logger,
        $connectionName = null
    ) {
        $this->metadataPool = $metadataPool;
        $this->entityManager = $entityManager;
        $this->filesystem = $filesystem;
        $this->logger = $logger;
        parent::__construct($context, $connectionName);
    }

    /**
     * @param AbstractModel $object
     * @param mixed $value
     * @param null $field
     * @return Offer|mixed
     */
    public function load(
        AbstractModel $object,
        $value,
        $field = null
    ) {
        return $this->entityManager->load($object, $value);
    }

    /**
     * @param AbstractModel $object
     * @return $this
     * @throws Exception
     */
    public function save(
        AbstractModel $object
    ): Offer {
        $this->beforeSave($object);
        $this->entityManager->save($object);
        $this->afterSave($object);
        return $this;
    }

    /**
     * @param DataObject $object
     */
    public function beforeSave(
        DataObject $object
    ) {
        $value = $object->getData(self::IMAGE);

        if ($this->fileResidesOutsideOffersDir($value)) {
            $value[0]['name'] = $value[0]['url'];
        }

        if ($imageName = $this->getUploadedImageName($value)) {
            $object->setData(self::IMAGE, $value);
            $object->setData(self::IMAGE, $imageName);
        } elseif (!is_string($value)) {
            $object->setData(self::IMAGE, null);
        }
        return parent::beforeSave($object);
    }

    /**
     * @param array $value
     * @return bool
     */
    private function fileResidesOutsideOffersDir(
        array $value
    ): bool {
        if (!is_array($value) || !isset($value[0]['url'])) {
            return false;
        }

        $fileUrl = ltrim($value[0]['url'], '/');
        $baseMediaDir = $this->filesystem->getUri(DirectoryList::MEDIA);

        return strpos($fileUrl, $baseMediaDir) === 0;
    }

    /**
     * @param array $value
     * @return string
     */
    private function getUploadedImageName(
        array $value
    ): string {
        if (is_array($value) && isset($value[0]['name'])) {
            return $value[0]['name'];
        }

        return '';
    }

    /**
     * @param DataObject $object
     */
    public function afterSave(
        DataObject $object
    ) {
        $value = $object->getData(self::IMAGE);

        try {
            $this->getImageUploader()->moveFileFromTmp($value);
        } catch (Exception $e) {
            $this->logger->critical($e);
        }

        return parent::afterSave($object);
    }

    /**
     * @return ImageUploader|mixed
     */
    private function getImageUploader()
    {
        if ($this->imageUploader === null) {
            $this->imageUploader = ObjectManager::getInstance()
                ->get(OfferImageUpload::class);
        }

        return $this->imageUploader;
    }

    /**
     * @param AbstractModel $object
     * @return $this|OfferInterface
     * @throws Exception
     */
    public function delete(
        AbstractModel $object
    ): Offer {
        $this->entityManager->delete($object);
        return $this;
    }

    /**
     * @param $id
     * @return array
     * @throws LocalizedException
     */
    public function lookupStoreIds(
        int $id
    ): array {
        $connection = $this->getConnection();

        $entityMetadata = $this->metadataPool->getMetadata(OfferInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = $connection->select()
            ->from(['os' => $this->getTable('dnd_offers_store')], 'store_id')
            ->join(
                ['o' => $this->getMainTable()],
                'os.offer_id = o.' . $linkField,
                []
            )
            ->where('o.' . $entityMetadata->getIdentifierField() . ' = :offer_id');

        return $connection->fetchCol($select, ['offer_id' => (int)$id]);
    }

    /**
     * @param $id
     * @return array
     * @throws LocalizedException
     */
    public function lookupCategoryIds(
        int $id
    ): array {
        $connection = $this->getConnection();

        $entityMetadata = $this->metadataPool->getMetadata(OfferInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = $connection->select()
            ->from(['os' => $this->getTable('dnd_offers_category')], 'category_id')
            ->join(
                ['o' => $this->getMainTable()],
                'os.offer_id = o.' . $linkField,
                []
            )
            ->where('o.' . $entityMetadata->getIdentifierField() . ' = :offer_id');

        return $connection->fetchCol($select, ['offer_id' => (int)$id]);
    }

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('dnd_offers', 'offer_id');
    }
}
