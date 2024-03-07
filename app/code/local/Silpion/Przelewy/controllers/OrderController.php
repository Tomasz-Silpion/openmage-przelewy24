<?php
/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 */
class Silpion_Przelewy_OrderController extends Mage_Core_Controller_Front_Action
{
    /**
     * @return void
     */
    public function redirectAction()
    {
        $orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

        $paymentData = Mage::helper('przelewy24')->getTransactionData(
            Mage::getSingleton('core/session')->getEncryptedSessionId(),
            $order->getIncrementId(),
            (int) $order->getGrandTotal() * 100,
            $order->getOrderCurrencyCode(),
            $order->getCustomerEmail(),
            $order->getBillingAddress()->getCountryId(),
        );

        $token = Mage::getModel('przelewy24/api')->createTransaction($paymentData);
        $redirectUrl = Mage::getModel('przelewy24/api')->getEndpointUrl("trnRequest/$token");

        Mage::app()->getResponse()->setRedirect($redirectUrl)->sendResponse();
    }
}
