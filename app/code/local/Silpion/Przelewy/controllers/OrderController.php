<?php
/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 */
class Silpion_Przelewy_OrderController extends Mage_Core_Controller_Front_Action
{
    /**
     * @return Mage_Core_Controller_Response_Http
     */
    public function redirectAction()
    {
        $orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        $sessionId = Mage::helper('przelewy24')->getSessionId($order);

        $transaction = Mage::helper('przelewy24')->createTransaction(
            $sessionId,
            $order->getIncrementId(),
            round($order->getGrandTotal() * 100),
            $order->getOrderCurrencyCode(),
            $order->getCustomerEmail(),
            $order->getBillingAddress()->getCountryId()
        );

        $transaction->setData('urlReturn', Mage::getUrl('przelewy24/order/return', ['sessionId' => $sessionId]));
        $transaction->setData('urlStatus', Mage::getUrl('przelewy24/order/status', ['id' => $order->getIncrementId()]));

        $token = Mage::getModel('przelewy24/api')->createTransaction($transaction)->getToken();
        $redirectUrl = Mage::getModel('przelewy24/api')->getEndpointUrl("trnRequest/$token");

        return Mage::app()->getResponse()->setRedirect($redirectUrl)->sendResponse();
    }

    /**
     * @return Mage_Core_Controller_Response_Http
     */
    public function repayAction()
    {
        $orderId = $this->getRequest()->getParam('orderId');
        $orderDate = $this->getRequest()->getParam('orderDate');

        $order = Mage::getModel('sales/order')->load($orderId);
        if ($order->getCreatedAt() === $orderDate) {
            Mage::getSingleton('checkout/session')->setLastRealOrderId($order->getIncrementId());

            $sessionId = Mage::helper('przelewy24')->getSessionId($order, round(time() / (10 * 60)) * (10 * 60));

            $transaction = Mage::helper('przelewy24')->createTransaction(
                $sessionId,
                $order->getIncrementId(),
                round($order->getGrandTotal() * 100),
                $order->getOrderCurrencyCode(),
                $order->getCustomerEmail(),
                $order->getBillingAddress()->getCountryId()
            );

            $transaction->setData('urlReturn', Mage::getUrl('przelewy24/order/return', ['sessionId' => $sessionId]));
            $transaction->setData('urlStatus', Mage::getUrl('przelewy24/order/status', ['id' => $order->getIncrementId()]));

            $token = Mage::getModel('przelewy24/api')->createTransaction($transaction)->getToken();
            $redirectUrl = Mage::getModel('przelewy24/api')->getEndpointUrl("trnRequest/$token");

            return Mage::app()->getResponse()->setRedirect($redirectUrl)->sendResponse();
        }

        $this->_forward('noRoute');
    }

    /**
     * @return Mage_Core_Controller_Response_Http
     */
    public function returnAction()
    {
        $sessionId = $this->getRequest()->getParam('sessionId');
        $sessionData = Mage::helper('przelewy24')->decodeSessionId($sessionId);
        $transaction = Mage::getModel('przelewy24/api')->getTransactionBySessionId($sessionId);
        if ($transaction->getStatus() && ($statement = $transaction->getStatement())) {
            if (!empty($sessionData['id'])) {
                $order = Mage::getModel('sales/order')->loadByIncrementId($sessionData['id']);

                $isCorrectAmount = Mage::helper('przelewy24')->isEqual($order->getGrandTotal(), $transaction->getAmount() / 100);
                if ($isCorrectAmount) {
                    $payment = $order->getPayment();

                    if ($payment && $payment->getLastTransId() !== $statement) {
                        $payment->setTransactionId($statement)
                            ->setCurrencyCode($order->getOrderCurrencyCode())
                            ->setShouldCloseParentTransaction(true)
                            ->setIsTransactionClosed(true)
                            ->registerCaptureNotification($order->getGrandTotal());

                        $order->save();
                    }

                    return $this->getResponse()->setRedirect(Mage::getUrl('checkout/onepage/success'));
                }
            }
        }

        return $this->getResponse()->setRedirect(Mage::getUrl('checkout/cart'));
    }

    /**
     * @return Mage_Core_Controller_Response_Http
     * @throws Exception
     */
    public function statusAction()
    {
        $json = file_get_contents("php://input");
        Mage::helper('przelewy24')->log($json);
        $payload = json_decode($json, true);
        if ($payload) {
            $sessionId = $payload['sessionId'];
            $sessionData = Mage::helper('przelewy24')->decodeSessionId($sessionId);
            if (!empty($sessionData['id'])) {
                $order = Mage::getModel('sales/order')->loadByIncrementId($sessionData['id']);
                $transactionResult = Mage::helper('przelewy24')->verifyTransaction(
                    $sessionId,
                    $payload['orderId'],
                    $payload['amount'],
                    $payload['currency']
                );

                if ($transactionResult) {
                    $transaction = Mage::getModel('przelewy24/api')->getTransactionBySessionId($sessionId);
                    if ($transaction->getStatus() && ($statement = $transaction->getStatement())) {
                        $payment = $order->getPayment();

                        if ($payment && $payment->getLastTransId() !== $statement) {
                            $payment->setTransactionId($statement)
                                    ->setCurrencyCode($order->getOrderCurrencyCode())
                                    ->setShouldCloseParentTransaction(true)
                                    ->setIsTransactionClosed(true)
                                    ->registerCaptureNotification($transaction->getAmount() / 100);

                            $order->save();
                        }

                        $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json', true);
                    }

                    return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode([
                        'status' => Silpion_Przelewy_Model_Api::STATUS_SUCCESS
                    ]));
                }
            }

            throw new Exception('Order not found');
        }
    }
}
