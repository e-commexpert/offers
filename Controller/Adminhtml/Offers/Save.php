<?php
declare(strict_types=1);

namespace Dnd\Offers\Controller\Adminhtml\Offers;

use Dnd\Offers\Model\OfferFactory;
use Dnd\Offers\Model\OfferRepository;
use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Cms\Model\Block;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;

class Save extends AbstractOffers
{
    /**
     * @var OfferRepository
     */
    private $offerRepository;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var
     */
    private $offerFactory;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param OfferRepository $offerRepository
     * @param RedirectFactory $redirectFactory
     * @param OfferFactory $offerFactory
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        OfferRepository $offerRepository,
        RedirectFactory $redirectFactory,
        offerFactory $offerFactory,
        DataPersistorInterface $dataPersistor
    ) {
        $this->offerRepository = $offerRepository;
        $this->redirectFactory = $redirectFactory;
        $this->offerFactory = $offerFactory;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context, $resultPageFactory);
    }

    /**
     * @return Redirect|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            if (empty($data['offer_id'])) {
                $data['offer_id'] = null;
            }

            /** @var Block $model */
            $model = $this->offerFactory->create();

            $id = (int)$this->getRequest()->getParam('offer_id');
            if ($id) {
                try {
                    $model = $this->offerRepository->getById($id);
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage(__('This offer no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
            }

            $model->setData($data);
            try {
                $this->offerRepository->save($model);
                $id = $model->getId();
                $this->messageManager->addSuccessMessage(__('The offer has been saved.'));
                $this->dataPersistor->clear('current_offer');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the offer.')
                );
            }

            $this->dataPersistor->set('current_offer', $data);
            if ($this->getRequest()->getParam('back') == 'edit' && $id) {
                $resultRedirect->setPath('*/*/edit/offer_id/' . $id);
            } else {
                $resultRedirect->setPath('*/*/');
            }
            return $resultRedirect;
        }
        return $resultRedirect->setPath('*/*/');
    }
}
