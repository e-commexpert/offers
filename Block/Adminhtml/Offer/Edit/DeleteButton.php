<?php
declare(strict_types=1);

namespace Dnd\Offers\Block\Adminhtml\Offer\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData(): array
    {
        if (!$this->getObjectId()) {
            return [];
        }
        return [
          'label'      => __('Delete offer'),
          'class'      => 'delete',
          'on_click'   => 'deleteConfirm( \'' . __(
              'Are you sure?'
          ) . '\', \'' . $this->getDeleteUrl() . '\')',
          'sort_order' => 20,
        ];
    }
}
