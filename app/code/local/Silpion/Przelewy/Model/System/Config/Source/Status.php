<?php

/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 */
class Silpion_Przelewy_Model_System_Config_Source_Status
{
    /**
     * @return array
     */
    public function toArray()
    {
        return [
            Silpion_Przelewy_Model_Api::STATUS_PROCESSING => Mage::helper('przelewy24')->__('Oczekiwanie'),
            Silpion_Przelewy_Model_Api::STATUS_SUCCESS => Mage::helper('przelewy24')->__('Powodzenie'),
            Silpion_Przelewy_Model_Api::STATUS_ERROR => Mage::helper('przelewy24')->__('Błąd'),
        ];
    }
}
