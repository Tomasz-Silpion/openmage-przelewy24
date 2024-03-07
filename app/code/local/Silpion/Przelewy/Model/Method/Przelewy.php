<?php
/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 */
class Silpion_Przelewy_Model_Method_Przelewy extends Mage_Payment_Model_Method_Abstract
{
    public const PAYMENT_METHOD_PRZELEWY24_CODE = 'przelewy24';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_PRZELEWY24_CODE;

    /**
     * Przelewy payment block paths
     *
     * @var string
     */
    protected $_formBlockType = 'przelewy24/form_przelewy';
    protected $_infoBlockType = 'przelewy24/info_przelewy';

    /**
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('przelewy24/order/redirect');
    }

    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getInstructions()
    {
        return trim($this->getConfigData('instructions'));
    }
}
