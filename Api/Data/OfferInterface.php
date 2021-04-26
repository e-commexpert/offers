<?php
declare(strict_types=1);

namespace Dnd\Offers\Api\Data;

interface OfferInterface
{
    const OFFER_ID = 'offer_id';

    const LABEL = 'label';

    const LINK = 'link';

    const DATE_FROM = 'date_from';

    const DATE_TO = 'date_to';

    /**
     * @return int|null
     */
    public function getOfferId(): ?int;

    /**
     * @return string|null
     */
    public function getLabel(): ?string;

    /**
     * @return string|null
     */
    public function getLink(): ?string;

    /**
     * @return string|null
     */
    public function getDateFrom(): ?string;

    /**
     * @return string|null
     */
    public function getDateTo(): ?string;

    /**
     * @param int $id
     * @return OfferInterface
     */
    public function setOfferId(
        int $id
    ): OfferInterface;

    /**
     * @param string $label
     * @return OfferInterface
     */
    public function setLabel(
        string $label
    ): OfferInterface;

    /**
     * @param string $link
     * @return OfferInterface
     */
    public function setLink(
        string $link
    ): OfferInterface;

    /**
     * @param string $dateFrom
     * @return OfferInterface
     */
    public function setDateFrom(
        string $dateFrom
    ): OfferInterface;

    /**
     * @param string $dateTo
     * @return OfferInterface
     */
    public function setDateTo(
        string $dateTo
    ): OfferInterface;
}
