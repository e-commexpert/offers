<?php
declare(strict_types=1);

namespace Dnd\Offers\Block\Adminhtml\Offer\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveAndContinueButton extends GenericButton implements ButtonProviderInterface
{
    const PERMISSION_RESSOURCE_NAME = 'Dnd_Offers::offers_edit';

    /**
     * @return array
     */
    public function getButtonData(): array
    {
        return [
          'label'          => __('Save and Continue Edit'),
          'class'          => 'save',
          'data_attribute' => [
            'mage-init' => [
              'button' => ['event' => 'saveAndContinueEdit'],
            ],
          ],
          'sort_order'     => 80,
        ];
    }
}
