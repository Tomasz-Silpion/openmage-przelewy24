<?php

/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 */
class Silpion_Przelewy_Adminhtml_PrzelewyPaymentController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @inheritDoc
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/silpion_przelewy');
    }

    /**
     * @interitDoc
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('system/silpion_przelewy');
        $this->_addContent($this->getLayout()->createBlock('przelewy24/adminhtml_payment'));
        $this->renderLayout();
    }

    /**
     * Edit payment action
     *
     * @return void
     */
    public function editAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('system/silpion_przelewy');

        $payment = Mage::getModel('przelewy24/payment')->load($this->getRequest()->getParam('id'));
        Mage::register('payment_data', $payment);

        $this->_addContent($this->getLayout()->createBlock('przelewy24/adminhtml_payment_edit'))->renderLayout();
    }

    /**
     * Save payment action
     *
     * @return void
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            try {
                $payment = Mage::getModel('przelewy24/payment')->load($this->getRequest()->getParam('id'));
                $payment->addData($this->getRequest()->getParams());
                $payment->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Payment was saved'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }

        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        try {
            $payment = Mage::getModel('przelewy24/payment')->load($this->getRequest()->getParam('id'));
            $payment->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Payment was deleted'));
            $this->_redirect('*/*/');
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
        }
    }
}
