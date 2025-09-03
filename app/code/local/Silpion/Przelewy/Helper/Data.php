<?php

/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 */
class Silpion_Przelewy_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Get object from session identifier
     *
     * @param string $sessionId
     * @return array
     */
    public function decodeSessionId($sessionId)
    {
        $sessionParams = explode('|', $sessionId);

        return [
           'id' => $sessionParams[0],
           'time' => $sessionParams[1],
        ];
    }

    /**
     * @param array $data
     * @return string
     */
    public function getSign($data)
    {
        $data['crc'] = $this->getConfig('crc');

        return hash('sha384', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /**
     * @param string $sessionId
     * @param string $description
     * @param float $amount
     * @param string $currency
     * @param string $email
     * @param string $country
     * @return Varien_Object
     */
    public function createTransaction(
        $sessionId,
        $description,
        $amount,
        $currency,
        $email,
        $country
    ) {
        return new Varien_Object([
            "merchantId" => (int) $this->getConfig('merchant_id'),
            "posId" => (int) $this->getConfig('merchant_id'),
            "sessionId" => $sessionId,
            "amount" => $amount,
            "currency" => $currency,
            "description" => $description,
            "email" => $email,
            "country" => $country,
            "language" => $this->getLanguageCode(),
            "urlReturn" => Mage::getUrl('checkout/onepage/success'),
            "urlStatus" => Mage::getUrl('checkout/cart'),
            "waitForResult" => true,
            "sign" => $this->getSign([
                'sessionId' => $sessionId,
                'merchantId' => (int) $this->getConfig('merchant_id'),
                'amount' => (int) $amount,
                'currency' => $currency,
            ]),
        ]);
    }

    /**
     * @param string $sessionId
     * @param int $orderId
     * @param float $amount
     * @param string $currency
     * @return bool
     */
    public function verifyTransaction($sessionId, $orderId, $amount, $currency)
    {
        $transaction = new Varien_Object([
            "merchantId" => (int) $this->getConfig('merchant_id'),
            "posId" => (int) $this->getConfig('merchant_id'),
            "orderId" => $orderId,
            "sessionId" => $sessionId,
            "amount" => $amount,
            "currency" => $currency,
            "sign" => $this->getSign([
                'sessionId' => $sessionId,
                'orderId' => $orderId,
                'amount' => (int) $amount,
                'currency' => $currency,
            ]),
        ]);

        return Mage::getModel('przelewy24/api')->verifyTransaction($transaction);
    }

    /**
     * Create unique but reproducible identifier
     *
     * @param Varien_Object
     * @param int|null $timestamp
     * @return string
     */
    public function getSessionId($object, $timestamp = null)
    {
        return ($object->getIncrementId() ? $object->getIncrementId() : $object->getId()) . '|' . ($timestamp ? $timestamp : strtotime($object->getCreatedAt()));
    }

    /**
     * @param float $a
     * @param float $b
     * @return bool
     */
    public function isEqual($a, $b)
    {
        return abs(((float) $a - (float) $b)) <= 0.01;
    }

    /**
     * @param mixed $data
     * @return void
     */
    public function log($data)
    {
        if (Mage::getModel('przelewy24/method_przelewy')->getConfigData('log')) {
            Mage::log($data, null, 'przelewy24.log', true);
        }
    }

    /**
     * @param string $path
     * @return string
     */
    private function getConfig($path)
    {
        return Mage::getModel('przelewy24/method_przelewy')->getConfigData($path);
    }

    /**
     * @return string
     */
    private function getLanguageCode()
    {
        return substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2);
    }
}
