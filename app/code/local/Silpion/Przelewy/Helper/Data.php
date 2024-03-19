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
     * @return string
     */
    public function decodeSessionId($sessionId)
    {
        return json_decode(base64_decode($sessionId), true);
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
    public function getTransaction(
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
                'merchantId'=> (int) $this->getConfig('merchant_id'),
                'amount' => (int) $amount,
                'currency' => $currency,
            ]),
        ]);
    }

    /**
     * Create unique but reproducible identifier
     *
     * @param Varien_Object
     * @param string|null $type
     * @return string
     */
    public function getSessionId($object, $type = null)
    {
        $data = [
            'id' => $object->getId(),
            'type' => $type ? $type : get_class($object),
            'time' => strtotime($object->getCreatedAt())
        ];

        return base64_encode(json_encode($data));
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
