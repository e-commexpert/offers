<?php
declare(strict_types=1);

namespace Dnd\Offers\Controller\Adminhtml\Offers;

use Dnd\Offers\Model\Offer;
use Dnd\Offers\Model\OfferRepository;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Edit extends AbstractOffers implements HttpGetActionInterface
{
    const ADMIN_RESOURCE = 'Dnd_Offers::offers_edit';
    /**
     * @var OfferRepository
     */
    private $offerRepository;
    /**
     * @var Registry $coreRegistry
     */
    private $coreRegistry;

    /**
     * Edit constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param OfferRepository $offerRepository
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        OfferRepository $offerRepository,
        Registry $registry
    ) {
        $this->coreRegistry = $registry;
        $this->offerRepository = $offerRepository;
        parent::__construct($context, $resultPageFactory);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('offer_id');

        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Block') : __('New offer'),
            $id ? __('Edit Block') : __('New offer')
        );
        if ($id) {
            $offer = $this->offerRepository->getById($id);
            $this->coreRegistry->unregister(Offer::ENTITY_REGISTRY_NAME);
            $this->coreRegistry->register(Offer::ENTITY_REGISTRY_NAME, $offer);
            $resultPage->getConfig()
                ->getTitle()
                ->prepend($offer->getId() ? __(
                    'Offer %1',
                    $offer->getLabel()
                ) : __('New offer'));
        } else {
            $resultPage->getConfig()
                ->getTitle()
                ->prepend(__('New offer'));
        }
        return $resultPage;
    }
}
