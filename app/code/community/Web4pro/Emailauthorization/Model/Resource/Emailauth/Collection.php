<?php

class Web4pro_Emailauthorization_Model_Resource_Emailauth_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('web4proemailauth/emailauth');
    }

}