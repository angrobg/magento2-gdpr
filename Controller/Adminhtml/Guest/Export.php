<?php
/**
 * Copyright © OpenGento, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Opengento\Gdpr\Controller\Adminhtml\Guest;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Opengento\Gdpr\Controller\Adminhtml\AbstractAction;
use Opengento\Gdpr\Model\Config;
use Opengento\Gdpr\Model\Export\ExportEntityData;

class Export extends AbstractAction
{
    public const ADMIN_RESOURCE = 'Opengento_Gdpr::order_export';

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var ExportEntityData
     */
    private $exportEntityData;

    public function __construct(
        Context $context,
        Config $config,
        FileFactory $fileFactory,
        ExportEntityData $exportEntityData
    ) {
        $this->fileFactory = $fileFactory;
        $this->exportEntityData = $exportEntityData;
        parent::__construct($context, $config);
    }

    protected function executeAction()
    {
        try {
            $entityId = (int) $this->getRequest()->getParam('id');

            return $this->fileFactory->create(
                'guest_privacy_data_' . $entityId . '.zip',
                [
                    'type' => 'filename',
                    'value' => $this->exportEntityData->export($entityId, 'order'),
                    'rm' => true,
                ],
                DirectoryList::TMP
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, new Phrase('An error occurred on the server.'));
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setRefererOrBaseUrl();
    }
}
