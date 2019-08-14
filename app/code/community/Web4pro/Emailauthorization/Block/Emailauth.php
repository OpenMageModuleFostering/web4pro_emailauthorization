<?php

class Web4pro_Emailauthorization_Block_Emailauth extends Mage_Core_Block_Template
{
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getEmailauthPostUrl()
    {
        return Mage::getUrl('emailauth/index/checkemail');
    }

    public function canShow()
    {
        if ($this->helper('web4proemailauth')->getConfig('showloginblock')) {
            return true;
        } else {
            return false;
        }
    }

}