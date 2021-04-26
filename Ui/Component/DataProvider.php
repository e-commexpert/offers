<?php
declare(strict_types=1);

namespace Dnd\Offers\Ui\Component;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Dnd\Offers\Model\ResourceModel\Offer\CollectionFactory;

class DataProvider extends AbstractDataProvider
{
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
        $this->collection = $collectionFactory->create();
    }
}
