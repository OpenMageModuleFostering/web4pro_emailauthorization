<?php

class Web4pro_Emailauthorization_Model_Resource_Emailauth extends Mage_Core_Model_Mysql4_Abstract
{

    public function _construct()
    {
        $this->_init('web4proemailauth/emailauthorization', 'id');
    }

}