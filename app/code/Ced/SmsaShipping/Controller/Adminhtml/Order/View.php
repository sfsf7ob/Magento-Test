<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_SmsaShipping
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\SmsaShipping\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;
use Psr\Log\LoggerInterface;

class View extends \Magento\Sales\Controller\Adminhtml\Order
{
   protected $_moduleReader;

   public function __construct(
        Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        OrderManagementInterface $orderManagement,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Module\Dir\Reader $moduleReader
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_fileFactory = $fileFactory;
        $this->_translateInline = $translateInline;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->orderManagement = $orderManagement;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
        $this->_scopeConfig = $scopeConfig;
        $this->_moduleReader = $moduleReader;
        parent::__construct($context, $coreRegistry, $fileFactory, $translateInline, $resultPageFactory, $resultJsonFactory, $resultLayoutFactory, $resultRawFactory, $orderManagement, $orderRepository, $logger);
    }

    public function execute()
    {
        $shipment_id = $this->getRequest()->getParam('shipment_ids');
        $model = $this->_objectManager->get('Magento\Sales\Model\Order\Shipment')->load($shipment_id);
        $order_id = $model->getOrder()->getIncrementId();
        header("content-type: application/pdf");
        header("Content-Disposition:inline;filename=$order_id.pdf");
        $alltrackback=$model->getAllTracks();
        foreach($alltrackback as $value)
        {
            $awb = $value->getNumber();
            break;
        }
        if($awb)
        {
            $pass_key = $this->_scopeConfig->getValue('carriers/smsashipping/passkey', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $params = array();
            $params["passKey"] = $pass_key;
            $params["awbNo"] = /*2900008125228*/$awb;
            $wsdlPath = $this->_moduleReader->getModuleDir('etc', 'Ced_SmsaShipping') . '/wsdl';
            $wsdl = $wsdlPath .'/'.'SMSAwebService.xml';
            $client = new \SoapClient($wsdl, array('trace' => 1));
            //$client = new \SoapClient('http://track.smsaexpress.com/SECOM/SMSAwebService.asmx?wsdl');
            $result = $client->getPDF($params);
            $pdf_result = $result->getPDFResult;
            print_r($pdf_result);
        }
    }
}
