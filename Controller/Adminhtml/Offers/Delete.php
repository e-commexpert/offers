<?php
declare(strict_types=1);

namespace Dnd\Offers\Controller\Adminhtml\Offers;

use Dnd\Offers\Model\OfferRepository;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;

class Delete extends Action
{
    const ADMIN_RESOURCE = 'Dnd_Offers::offers_delete';

    /**
     * @var OfferRepository
     */
    private $offerRepository;

    /**
     * Delete constructor.
     *
     * @param OfferRepository $offerRepository
     * @param Context $context
     */
    public function __construct(
        OfferRepository $offerRepository,
        Context $context
    ) {
        $this->offerRepository = $offerRepository;
        parent::__construct($context);
    }

    /**
     * @return Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('offer_id');
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $this->offerRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('You have deleted the offer.'));
                return $resultRedirect->setPath('*/*/');
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath(
                    '*/*/edit',
                    ['offer_id' => $id]
                );
            }
        }
        $this->messageManager->addErrorMessage(__('We can not find an offer to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
