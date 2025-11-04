<?php

/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 */
class Silpion_Przelewy_Block_Adminhtml_Payment_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Configure edit form container
     */
    public function __construct()
    {
        $this->_objectId   = 'id';
        $this->_blockGroup = 'przelewy24';
        $this->_controller = 'adminhtml_payment';
        parent::__construct();
        $this->_updateButton('save', 'label', Mage::helper('przelewy24')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('przelewy24')->__('Delete'));
    }

    /**
     * Header text getter
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('payment_data') && Mage::registry('payment_data')->getId()) {
            return Mage::helper('przelewy24')->__(
                "Edit Payment '%s'",
                $this->htmlEscape(Mage::registry('payment_data')->getId())
            );
        }

        return '';
    }
}
