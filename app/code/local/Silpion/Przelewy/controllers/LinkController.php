<?php

/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 */
class Silpion_Przelewy_LinkController extends Mage_Core_Controller_Front_Action
{
    /**
     * @return Mage_Core_Controller_Response_Http
     */
    public function generateAction()
    {
        $store = Mage::app()->getStore();
        $paymentData = [
            'entity_id' => $this->getRequest()->getParam('id'),
            'entity_type' => 'link',
            'amount' => $this->getRequest()->getParam('amount'),
            'currency_code' => $this->getRequest()->getParam('currency') ?? $store->getCurrentCurrencyCode(),
            'email' => $this->getRequest()->getParam('email') ?? Mage::getStoreConfig('trans_email/ident_general/email', $store),
            'status' => Silpion_Przelewy_Model_Api::STATUS_PROCESSING,
        ];

        $payment = Mage::getModel('przelewy24/payment')->setData($paymentData);
        $payment->save();
        $sessionId = Mage::helper('przelewy24')->getSessionId($payment);
        $payment->setSessionId($sessionId)->save();

        $transaction = Mage::helper('przelewy24')->createTransaction(
            $sessionId,
            $payment->getId(),
            round($payment->getAmount() * 100),
            $payment->getCurrencyCode(),
            $payment->getEmail(),
            substr(Mage::getStoreConfig('general/locale/code', $store), 3)
        );

        $transaction->setData('urlReturn', Mage::getUrl('przelewy24/link/return', ['id' => $payment->getId(), 'sessionId' => $sessionId]));
        $transaction->setData('urlStatus', Mage::getUrl('przelewy24/link/status', ['id' => $payment->getId()]));

        $token = Mage::getModel('przelewy24/api')->createTransaction($transaction)->getToken();
        $redirectUrl = Mage::getModel('przelewy24/api')->getEndpointUrl("trnRequest/$token");

        $response = $this->getResponse()->setBody(Mage::helper('core')->jsonEncode([
            'link' => $redirectUrl
        ]));

        return $response;
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
                $payment = Mage::getModel('przelewy24/payment')->load($sessionData['id']);

                $isCorrectAmount = Mage::helper('przelewy24')->isEqual($payment->getAmount(), $transaction->getAmount() / 100);
                if ($isCorrectAmount) {
                    $payment->setTransactionId($statement)
                            ->setStatus(Silpion_Przelewy_Model_Api::STATUS_SUCCESS)
                            ->save();
                    Mage::getSingleton('core/session')->addSuccess(Mage::helper('przelewy24')->__('Dziękujemy za płatność!'));
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
                $payment = Mage::getModel('przelewy24/payment')->load($sessionData['id']);
                $transactionResult = Mage::helper('przelewy24')->verifyTransaction(
                    $sessionId,
                    $payload['orderId'],
                    $payload['amount'],
                    $payload['currency']
                );

                if ($transactionResult) {
                    $transaction = Mage::getModel('przelewy24/api')->getTransactionBySessionId($sessionId);
                    if ($transaction->getStatus() && ($statement = $transaction->getStatement())) {
                        $payment->setTransactionId($statement)
                                ->setStatus(Silpion_Przelewy_Model_Api::STATUS_SUCCESS)
                                ->save();
                        $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json', true);
                    }

                    return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode([
                        'status' => Silpion_Przelewy_Model_Api::STATUS_SUCCESS
                    ]));
                }
            }

            throw new Exception('Payment not found');
        }
    }
}
