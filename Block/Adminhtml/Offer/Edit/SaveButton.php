<?php
declare(strict_types=1);

namespace Dnd\Offers\Block\Adminhtml\Offer\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveButton extends GenericButton implements ButtonProviderInterface
{

    const PERMISSION_RESSOURCE_NAME = 'Dnd_Offers::offers_edit';

    /**
     * @return array
     */
    public function getButtonData(): array
    {
        return [
          'label'          => __('Save offer'),
          'class'          => 'save primary',
          'data_attribute' => [
            'mage-init' => ['button' => ['event' => 'save']],
            'form-role' => 'save',
          ],
          'sort_order'     => 90,
        ];
    }
}
