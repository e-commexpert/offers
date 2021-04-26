<?php
declare(strict_types=1);

namespace Dnd\Offers\Ui\Component\Listing\Column;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class PageActions extends Column
{
    /**
     * ACL resource name
     */
    const PERMISSION_RESOURCE_NAME = 'Dnd_Offers::offers_edit';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var AuthorizationInterface
     */
    private $auth;

    /**
     * Actions constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param AuthorizationInterface $authorization
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        AuthorizationInterface $authorization,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->auth = $authorization;
        $data = $this->_isAllowed() ? $data : [];
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return $this->auth->isAllowed(self::PERMISSION_RESOURCE_NAME);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(
        array $dataSource
    ): array {
        if ($this->_isAllowed() && isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')]['edit'] = [
                    'href' => $this->urlBuilder->getUrl(
                        "offers/offers/edit",
                        ['offer_id' => $item['offer_id']]
                    ),
                    'label' => __('Edit'),
                    'hidden' => false,
                ];
            }
        }

        return $dataSource;
    }
}
