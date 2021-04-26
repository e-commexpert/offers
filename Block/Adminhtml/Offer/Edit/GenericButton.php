<?php
declare(strict_types=1);

namespace Dnd\Offers\Block\Adminhtml\Offer\Edit;

use Magento\Backend\Block\Widget\Context;

class GenericButton
{
    /**
     * GenericButton constructor.
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getBackUrl(): string
    {
        return $this->getUrl('*/*/');
    }

    /**
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl(
        string $route = '',
        array $params = []
    ): string {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }

    /**
     * @return string
     */
    public function getDeleteUrl(): string
    {
        return $this->getUrl(
            '*/*/delete',
            ['offer_id' => $this->getObjectId()]
        );
    }

    /**
     * @return mixed
     */
    public function getObjectId()
    {
        return $this->context->getRequest()->getParam('offer_id');
    }
}
