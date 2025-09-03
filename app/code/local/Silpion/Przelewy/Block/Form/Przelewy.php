<?php

/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 *
 * Block for Przelewy24 payment method form
 */
class Silpion_Przelewy_Block_Form_Przelewy extends Mage_Payment_Block_Form
{
    /**
     * Instructions text
     *
     * @var string|null
     */
    protected $_instructions;

    /**
     * Block construction. Set block template.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('payment/form/przelewy24.phtml');
    }

    /**
     * Get instructions text from config
     *
     * @return string
     */
    public function getInstructions()
    {
        if (is_null($this->_instructions)) {
            $this->_instructions = $this->getMethod()->getInstructions();
        }
        return $this->_instructions;
    }
}
