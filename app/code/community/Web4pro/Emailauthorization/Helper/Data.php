<?php

class Web4pro_Emailauthorization_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getCustomerByEmail($email)
    {
        $customer = Mage::getModel("customer/customer");
        $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
        $customer->loadByEmail($email);

        return $customer;
    }

    public function getCustomerById($id)
    {
        $customer = Mage::getModel("customer/customer");
        $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
        $customer->load($id);

        return $customer;
    }

    public function sendEmail($mailInfo = array(), $bodyData = array())
    {
        $emailTplVar = array(
            'token_url' => Mage::getUrl('emailauth/index/checktoken', array('token' => $bodyData['token'])),
            'tokenlifetime' => $bodyData['tokenlifetime'],
        );

        $mailer = Mage::getModel('core/email_template_mailer');
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($mailInfo['email'], $mailInfo['fullname']);
        $mailer->addEmailInfo($emailInfo);

        $mailer->setSender('general');
        $mailer->setStoreId(Mage::app()->getStore()->getId());
        if (version_compare(Mage::getVersion(), '1.9.1') > 0) {
            // app/locale/en_US/template/email/web4proemailauth/token_email.html
            $mailer->setTemplateId('web4proemailauth_email_template'); // Magento >= 1.9.1
        } else {
            $mailer->setTemplateId('web4proemailauth_email_template_v18'); // Magento <= 1.8
        }
        $mailer->setTemplateParams($emailTplVar);
        $mailer->send();
    }

    public function getConfig($field = '', $group = 'general', $section = 'web4proemailauth')
    {
        if (empty($field)) {
            return null;
        }

        return Mage::getStoreConfig("{$section}/{$group}/$field");
    }

    public function getEmailConfirmationUrl($email = null)
    {
        return Mage::getUrl('customer/account/confirmation', array('email' => $email));
    }

}