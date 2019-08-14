<?php

class Web4pro_Emailauthorization_Model_Emailauth extends Mage_Core_Model_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('web4proemailauth/emailauth');
    }

}