<?php 
namespace Magecomp\Smshisms\Helper;

class Apicall extends \Magento\Framework\App\Helper\AbstractHelper
{
	const XML_HISMS_API_SENDERID = 'smspro/smsgatways/hismssenderid';
    const XML_HISMS_API_USERNAME = 'smspro/smsgatways/hismsusername';
	const XML_HISMS_API_URL = 'smspro/smsgatways/hismsapiurl';
    const XML_HISMS_API_PASSWORD = 'smspro/smsgatways/hismspassword';

	public function __construct(\Magento\Framework\App\Helper\Context $context)
	{
		parent::__construct($context);
	}

    public function getTitle() {
        return __("Hisms");
    }

    public function getApiSenderId(){
        return $this->scopeConfig->getValue(
            self::XML_HISMS_API_SENDERID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getUsername() {
        return $this->scopeConfig->getValue(
            self::XML_HISMS_API_USERNAME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getPassword()	{
        return $this->scopeConfig->getValue(
            self::XML_HISMS_API_PASSWORD,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

	public function getApiUrl()	{
		return $this->scopeConfig->getValue(
            self::XML_HISMS_API_URL,
			 \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}

	public function validateSmsConfig() {
        return $this->getApiUrl() && $this->getUsername() && $this->getApiSenderId();
    }

	public function callApiUrl($mobilenumbers,$message)
	{
        try
        {
        $url = "https://www.hisms.ws/api.php";
            $username = $this->getUsername();
            $senderid = $this->getApiSenderId();
            $password = $this->getPassword();
			
			$om = \Magento\Framework\App\ObjectManager::getInstance();
			$storeManager = $om->get('Psr\Log\LoggerInterface');
			$storeManager->info('Magecomp Log');
			
			$storeManager->info($url);
			$storeManager->info($username);
			$storeManager->info($password);
			$storeManager->info($senderid);


            $ch = curl_init();
            if (!$ch)
            {
                return "Couldn't initialize a cURL handle";
            }
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt ($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt ($ch, CURLOPT_POSTFIELDS,
            "send_sms&username=966566666314&password=tanza2020&numbers=$mobilenumbers&sender=tanza.sa&message=$message");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $curlresponse = curl_exec($ch); // execute

            if(curl_errno($ch))
            {
                curl_close($ch);
                return 'Error: '.curl_error($ch);
            }
            curl_close($ch);
            return true;
        }
        catch (\Exception $e) {
            return $e->getMessage();
        }
	}
}