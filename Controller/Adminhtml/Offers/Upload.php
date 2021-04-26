<?php
declare(strict_types=1);

namespace Dnd\Offers\Controller\Adminhtml\Offers;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultInterface;

class Upload extends AbstractOffers implements HttpPostActionInterface
{
    /**
     * @var ImageUploader
     */
    private $imageUploader;

    /**
     * Upload constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ImageUploader $imageUploader
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ImageUploader $imageUploader
    ) {
        parent::__construct($context, $resultPageFactory);
        $this->imageUploader = $imageUploader;
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $imageId = $this->_request->getParam('param_name', 'image');

        try {
            $result = $this->imageUploader->saveFileToTmpDir($imageId);
        } catch (Exception $e) {
            $result = ['error' => $e->getMessage(),
                'errorcode' => $e->getCode(),
            ];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)
            ->setData($result);
    }
}
