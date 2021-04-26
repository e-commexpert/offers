<?php
declare(strict_types=1);

namespace Dnd\Offers\Api;

use Dnd\Offers\Api\Data\OfferInterface;

interface OfferRepositoryInterface
{
    /**
     * @param int $id
     * @return OfferInterface
     */
    public function getById(
        int $id
    ): OfferInterface;

    /**
     * @param OfferInterface $offer
     * @return OfferInterface
     */
    public function save(
        OfferInterface $offer
    ): OfferInterface;

    /**
     * @param int $id
     * @return mixed
     */
    public function deleteById(
        int $id
    );
}
