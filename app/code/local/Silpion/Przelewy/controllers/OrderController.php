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

        $transaction = Mage::helper('przelewy24')->getTransaction(
            Mage::helper('przelewy24')->getSessionId($order),
            $order->getIncrementId(),
            (int) $order->getGrandTotal() * 100,
            $order->getOrderCurrencyCode(),
            $order->getCustomerEmail(),
            $order->getBillingAddress()->getCountryId(),
        );

        $transaction->setData('urlReturn', Mage::getUrl('przelewy24/order/return', ['id' => $order->getIncrementId()]));

        $token = Mage::getModel('przelewy24/api')->createTransaction($transaction)->getToken();
        $redirectUrl = Mage::getModel('przelewy24/api')->getEndpointUrl("trnRequest/$token");

        Mage::app()->getResponse()->setRedirect($redirectUrl)->sendResponse();
    }

    /**
     * @return Mage_Core_Controller_Response_Http
     */
    public function returnAction()
    {
        $orderId = $this->getRequest()->getParam('id');
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

        $sessionId = Mage::helper('przelewy24')->getSessionId($order);
        $transaction = Mage::getModel('przelewy24/api')->getTransactionBySessionId($sessionId);

        if ($transaction->getStatus() && ($statement = $transaction->getStatement())) {
            $payment = $order->getPayment();
            $payment->setTransactionId($statement)
                ->setCurrencyCode($order->getOrderCurrencyCode())
                ->setShouldCloseParentTransaction(true)
                ->setIsTransactionClosed(true)
                ->registerCaptureNotification($order->getGrandTotal());

            $order->save();

            return $this->getResponse()->setRedirect(Mage::getUrl('checkout/onepage/success'));
        }

        return $this->getResponse()->setRedirect(Mage::getUrl('checkout/cart'));
    }
}
