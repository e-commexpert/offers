<?php
declare(strict_types=1);

namespace Dnd\Offers\Model;

use Dnd\Offers\Api\Data\OfferInterface;
use Dnd\Offers\Api\OfferRepositoryInterface;
use Dnd\Offers\Model\ResourceModel\Offer as ResourceOffer;
use Exception;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class OfferRepository implements OfferRepositoryInterface
{

    /**
     * @var OfferFactory
     */
    private $offerFactory;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var ResourceOffer
     */
    private $resource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * OfferRepository constructor.
     *
     * @param OfferFactory $offerFactory
     * @param DataPersistorInterface $dataPersistor
     * @param ResourceOffer $resource
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        OfferFactory $offerFactory,
        DataPersistorInterface $dataPersistor,
        ResourceOffer $resource,
        StoreManagerInterface $storeManager
    ) {
        $this->offerFactory = $offerFactory;
        $this->dataPersistor = $dataPersistor;
        $this->resource = $resource;
        $this->storeManager = $storeManager;
    }

    /**
     * @param OfferInterface $offer
     * @return OfferInterface
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function save(
        OfferInterface $offer
    ): OfferInterface {
        if (empty($offer->getStoreId())) {
            $offer->setStoreId($this->storeManager->getStore()->getId());
        }

        try {
            $this->resource->save($offer);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $offer;
    }

    /**
     * @param $id
     * @return mixed|void
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(
        int $id
    ) {
        $this->delete($this->getById($id));
    }

    /**
     * @param OfferInterface $offer
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(
        OfferInterface $offer
    ): bool {
        try {
            $offer->getResource()->delete($offer);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @param int $id
     * @return OfferInterface
     * @throws NoSuchEntityException
     */
    public function getById(
        int $id
    ): OfferInterface {
        $offer = $this->offerFactory->create();
        $this->resource->load($offer, $id);
        if (!$offer->getId()) {
            throw new NoSuchEntityException(__(
                'Offer with id %1 does not exist.',
                $id
            ));
        }
        return $offer;
    }
}
