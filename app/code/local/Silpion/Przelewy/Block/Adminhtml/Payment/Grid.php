<?php

/**
 * @author Tomasz Gregorczyk <tomasz@silpion.com.pl>
 */
class Silpion_Przelewy_Block_Adminhtml_Payment_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return Silpion_Przelewy_Block_Adminhtml_Payment_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('przelewy24/payment')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return Silpion_Przelewy_Block_Adminhtml_Payment_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('payment_id', array(
            'header' => Mage::helper('przelewy24')->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'type' =>  'number',
            'index' => 'payment_id',
        ));

        $this->addColumn('entity_type', array(
            'header' => Mage::helper('przelewy24')->__('Entity Type'),
            'width' => '120px',
            'index' => 'entity_type',
        ));

        $this->addColumn('entity_id', array(
            'header' => Mage::helper('przelewy24')->__('Entity ID'),
            'width' => '120px',
            'index' => 'name',
            'type'  => 'number',
        ));

        $this->addColumn('session_id', array(
            'header' => Mage::helper('przelewy24')->__('Session ID'),
            'width' => '120px',
            'index' => 'session_id',
        ));

        $this->addColumn('amount', [
            'header' => Mage::helper('przelewy24')->__('Amount'),
            'index' => 'amount',
            'type'  => 'currency',
            'currency' => 'currency_code',
        ]);

        $this->addColumn('transaction_id', array(
            'header' => Mage::helper('przelewy24')->__('Transaction ID'),
            'width' => '120px',
            'index' => 'transaction_id',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('przelewy24')->__('Status'),
            'width' => '120px',
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::getModel('przelewy24/system_config_source_status')->toArray(),
        ));

        $this->addColumn('created_at', array(
            'header' => Mage::helper('przelewy24')->__('Created At'),
            'align' => 'left',
            'width' => '100px',
            'type' => 'datetime',
            'index' => 'created_at',
        ));

        $this->addColumn('updated_at', array(
            'header' => Mage::helper('przelewy24')->__('Updated At'),
            'align' => 'left',
            'width' => '100px',
            'type' => 'datetime',
            'index' => 'updated_at',
        ));

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getRowUrl($row)
    {
        return '#';
    }
}
