<?php
declare(strict_types=1);

namespace Dnd\Offers\Model;

use Dnd\Offers\Api\Data\OfferInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class Offer extends AbstractModel implements OfferInterface, IdentityInterface
{
    /**
     * @var string
     */
    const CACHE_TAG = 'dnd_offers_offer';

    /**
     * @var string
     */
    const ENTITY_REGISTRY_NAME = 'current_offer';

    /**
     * Path in /pub/media directory
     */
    const ENTITY_MEDIA_PATH = '/offers/offer';

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Offer constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param StoreManagerInterface $storeManager
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        StoreManagerInterface $storeManager,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @return string[]
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return int|null
     */
    public function getOfferId(): ?int
    {
        return $this->getData(self::OFFER_ID);
    }

    /**
     * @return string|null
     */
    public function getLabel(): ?string
    {
        return (string)$this->getData(self::LABEL);
    }

    /**
     * @return string|null
     */
    public function getLink(): ?string
    {
        return (string)$this->getData(self::LINK);
    }

    /**
     * @return string|null
     */
    public function getDateFrom(): ?string
    {
        return $this->getData(self::DATE_FROM);
    }

    /**
     * @return string|null
     */
    public function getDateTo(): ?string
    {
        return $this->getData(self::DATE_TO);
    }

    /**
     * @param int $id
     * @return OfferInterface
     */
    public function setOfferId(
        int $id
    ): OfferInterface {
        return $this->setData(self::OFFER_ID, $id);
    }

    /**
     * @param string $label
     * @return OfferInterface
     */
    public function setLabel(
        string $label
    ): OfferInterface {
        return $this->setData(self::LABEL, $label);
    }

    /**
     * @param string $link
     * @return OfferInterface
     */
    public function setLink(
        string $link
    ): OfferInterface {
        return $this->setData(self::LINK, $link);
    }

    /**
     * @param string $dateFrom
     * @return OfferInterface
     */
    public function setDateFrom(
        string $dateFrom
    ): OfferInterface {
        return $this->setData(self::DATE_FROM, $dateFrom);
    }

    /**
     * @param string $dateTo
     * @return OfferInterface|Offer
     */
    public function setDateTo(
        string $dateTo
    ): OfferInterface {
        return $this->setData(self::DATE_TO, $dateTo);
    }

    /**
     * @return array
     */
    public function getStores(): array
    {
        return $this->hasData('stores') ? $this->getData('stores') : $this->getData('store_id');
    }

    /**
     * @param string $attributeCode
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getImageUrl(
        string $attributeCode = 'image'
    ): string {
        $url = '';
        $image = $this->getData($attributeCode);
        if ($image) {
            if (is_string($image)) {
                $store = $this->storeManager->getStore();

                $isRelativeUrl = substr($image, 0, 1) === '/';

                $mediaBaseUrl = $store->getBaseUrl(
                    UrlInterface::URL_TYPE_MEDIA
                );

                if ($isRelativeUrl) {
                    $url = $image;
                } else {
                    $url = $mediaBaseUrl
                        . ltrim(self::ENTITY_MEDIA_PATH, '/')
                        . '/'
                        . $image;
                }
            } else {
                throw new LocalizedException(
                    __('Something went wrong while getting the image url.')
                );
            }
        }
        return $url;
    }

    protected function _construct()
    {
        $this->_init(\Dnd\Offers\Model\ResourceModel\Offer::class);
    }
}
