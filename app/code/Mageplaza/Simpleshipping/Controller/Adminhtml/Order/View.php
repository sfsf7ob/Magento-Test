<?php

namespace Mageplaza\Simpleshipping\Controller\Adminhtml\Order;

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
          $ship_number = $awb;
          $ch = curl_init();
               curl_setopt($ch, CURLOPT_URL, "https://www.speedlineship.com/partner/api/4fbdb49c001fbd589738135f90719a3efc12640cba11e4f38c8d14703d5f90f0/label/");
               curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
               curl_setopt($ch, CURLOPT_HEADER, 0);
               curl_setopt($ch, CURLOPT_TIMEOUT, 5);
               curl_setopt($ch, CURLOPT_POST, 1);
               curl_setopt($ch, CURLOPT_POSTFIELDS, "ship_number=$ship_number&ship_number=labela4&output_type=download");
               if(curl_errno($ch)){
                       throw new Exception(curl_error($ch));
                   }

              $result = curl_exec($ch);
              $obj = json_decode($result);
            //$client = new \SoapClient('http://track.smsaexpress.com/SECOM/SMSAwebService.asmx?wsdl');
            //$pdf_result = $obj->response_url;
            print_r($result);
        }
    }
}
