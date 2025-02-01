<?php
/**
 * class Silpion_Przelewy_Block_Adminhtml_Link
 */
class Silpion_Przelewy_Block_Adminhtml_Payment extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_objectId = "id";
        $this->_controller = 'adminhtml_payment';
        $this->_blockGroup = 'przelewy24';
        $this->_headerText = Mage::helper('przelewy')->__('Payments');
        parent::__construct();
    }
}
