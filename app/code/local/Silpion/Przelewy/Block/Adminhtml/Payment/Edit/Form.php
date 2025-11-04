<?php

/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 */
class Silpion_Przelewy_Block_Adminhtml_Payment_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Build form fields
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form([
            'id'     => 'edit_form',
            'action' => $this->getUrl('*/*/save', ['id' => $this->getRequest()->getParam('id')]),
            'method' => 'post'
        ]);

        $fieldset = $form->addFieldset('payment_form', ['legend' => Mage::helper('przelewy24')->__('Details')]);

        $fieldset->addField('entity_type', 'text', [
            'label'    => Mage::helper('przelewy24')->__('Entity Type'),
            'required' => false,
            'name'     => 'entity_type',
        ]);

        $fieldset->addField('entity_id', 'text', [
            'label'    => Mage::helper('przelewy24')->__('Entity ID'),
            'required' => false,
            'name'     => 'entity_id',
        ]);

        $fieldset->addField('session_id', 'text', [
            'label'    => Mage::helper('przelewy24')->__('Session ID'),
            'required' => false,
            'name'     => 'session_id',
        ]);

        $fieldset->addField('amount', 'text', [
            'label'    => Mage::helper('przelewy24')->__('Amount'),
            'required' => false,
            'name'     => 'amount',
        ]);

        $fieldset->addField('transaction_id', 'text', [
            'label'    => Mage::helper('przelewy24')->__('Transaction ID'),
            'required' => false,
            'name'     => 'transaction_id',
        ]);

        $fieldset->addField('status', 'select', [
            'label'    => Mage::helper('przelewy24')->__('Status'),
            'required' => false,
            'name'     => 'status',
            'options' => Mage::getModel('przelewy24/system_config_source_status')->toArray(),
        ]);

        if (Mage::registry('payment_data')) {
            $form->setValues(Mage::registry('payment_data')->getData());
        }

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
