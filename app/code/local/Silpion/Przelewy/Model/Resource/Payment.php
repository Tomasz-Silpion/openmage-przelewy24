<?php
/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 */
class Silpion_Przelewy_Model_Resource_Payment extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('przelewy24/payment', 'payment_id');
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getCreatedAt()) {
            $object->setCreatedAt(Varien_Date::now());
        } else {
            $object->setUpdatedAt(Varien_Date::now());
        }

        return parent::_beforeSave($object);
    }
}
