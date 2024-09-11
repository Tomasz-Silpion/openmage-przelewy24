<?php
/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 */
class Silpion_Przelewy_Model_Api extends Mage_Payment_Model_Method_Abstract
{
    /**
     * @var string
     */
    public const PRODUCTION_URL = 'https://secure.przelewy24.pl/';

    /**
     * @var string
     */
    public const SANDBOX_URL = 'https://sandbox.przelewy24.pl/';

    /**
     * @var string
     */
    public const STATUS_SUCCESS = 'success';

    /**
     * @param Varien_Object $transaction
     * @return Varien_Object
     * @throws Mage_Core_Exception
     */
    public function createTransaction($transaction)
    {
        $client = new Zend_Http_Client();
        $client->setUri($this->getEndpointUrl('api/v1/transaction/register'));
        $client->setMethod(Zend_Http_Client::POST);

        $client->setParameterPost($transaction->getData());
        $client->setHeaders('Content-Type', 'application/json');
        $client->setHeaders('Authorization', 'Basic ' . $this->getBasicAuth());
        $response = $client->request();

        $responseBody = $response->getBody();
        if ($responseBody) {
            $responseData = json_decode($responseBody, true);
            if (!empty($responseData['error'])) {
                Mage::throwException($responseData['error']);
            }
        }

        if (!empty($responseData['data']['token'])) {
            return $transaction->setToken($responseData['data']['token']);
        }

        Mage::throwException($response->getMessage());
    }

    /**
     * @param string $sessionId
     * @return string
     * @throws Mage_Core_Exception
     */
    public function getTransactionBySessionId($sessionId)
    {
        $client = new Zend_Http_Client();
        $sessionId = urlencode($sessionId);
        $client->setUri($this->getEndpointUrl("api/v1/transaction/by/sessionId/$sessionId"));
        $client->setMethod(Zend_Http_Client::GET);

        $client->setHeaders('Content-Type', 'application/json');
        $client->setHeaders('Authorization', 'Basic ' . $this->getBasicAuth());
        $response = $client->request();

        $responseBody = $response->getBody();

        if ($responseBody) {
            $responseData = json_decode($responseBody, true);
            if (!empty($responseData['error'])) {
                Mage::throwException($responseData['error']);
            }
        }

        if (!empty($responseData['data'])) {
            $transaction = new Varien_Object();
            return $transaction->setData($responseData['data']);
        }

        Mage::throwException($response->getMessage());
    }

    /**
     * @param Varien_Object $transaction
     * @return Varien_Object
     * @throws Mage_Core_Exception
     */
    public function verifyTransaction($transaction)
    {
        $client = new Zend_Http_Client();
        $client->setUri($this->getEndpointUrl('api/v1/transaction/verify'));
        $client->setMethod(Zend_Http_Client::PUT);

        $client->setParameterPost($transaction->getData());
        $client->setHeaders('Content-Type', 'application/json');
        $client->setHeaders('Authorization', 'Basic ' . $this->getBasicAuth());
        $response = $client->request();

        $responseBody = $response->getBody();
        if ($responseBody) {
            $responseData = json_decode($responseBody, true);
            if (!empty($responseData['error'])) {
                Mage::throwException($responseData['error']);
            }
        }

        if (!empty($responseData['data']['status'])) {
            return $responseData['data']['status'] === self::STATUS_SUCCESS;
        }

        Mage::throwException($response->getMessage());
    }

    /**
     * @return bool
     */
    public function testAccess()
    {
        $client = new Zend_Http_Client();
        $client->setUri($this->getEndpointUrl('api/v1/testAccess'));
        $client->setHeaders('Authorization', 'Basic ' . $this->getBasicAuth());
        $response = $client->request();

        if ($response->isSuccessful()) {
            return true;
        }

        return false;
    }

    /**
     * @param string $path
     * @return string
     */
    public function getEndpointUrl($path)
    {
        if (Mage::getModel('przelewy24/method_przelewy')->getConfigData('sandbox')) {
            return self::SANDBOX_URL . $path;
        }

        return self::PRODUCTION_URL . $path;
    }

    /**
     * @return string
     */
    private function getBasicAuth()
    {
        $username = Mage::getModel('przelewy24/method_przelewy')->getConfigData('merchant_id');
        $password = Mage::getModel('przelewy24/method_przelewy')->getConfigData('reports_key');

        return base64_encode("$username:$password");
    }
}
