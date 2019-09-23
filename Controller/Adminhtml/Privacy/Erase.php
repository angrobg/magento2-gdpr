<?php
/**
 * Copyright © OpenGento, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Opengento\Gdpr\Controller\Adminhtml\Privacy;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Opengento\Gdpr\Api\EraseEntityManagementInterface;
use Opengento\Gdpr\Controller\Adminhtml\AbstractAction;
use Opengento\Gdpr\Model\Config;

class Erase extends AbstractAction implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Opengento_Gdpr::customer_erase';

    /**
     * @var EraseEntityManagementInterface
     */
    private $eraseManagement;

    public function __construct(
        Context $context,
        Config $config,
        EraseEntityManagementInterface $eraseManagement
    ) {
        $this->eraseManagement = $eraseManagement;
        parent::__construct($context, $config);
    }

    protected function executeAction()
    {
        try {
            $this->eraseManagement->process(
                $this->eraseManagement->create((int) $this->getRequest()->getParam('id'), 'customer')
            );
            $this->messageManager->addSuccessMessage(new Phrase('You erased the customer.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, new Phrase('An error occurred on the server.'));
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('customer/index');
    }
}
