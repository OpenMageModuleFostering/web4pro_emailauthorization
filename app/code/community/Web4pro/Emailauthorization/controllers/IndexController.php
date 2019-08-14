<?php

require_once('app/code/core/Mage/Customer/controllers/AccountController.php');

class Web4pro_Emailauthorization_IndexController extends Mage_Customer_AccountController
{
    public function preDispatch()
    {
        Mage_Core_Controller_Front_Action::preDispatch();

        if (!$this->getRequest()->isDispatched()) {
            return;
        }

        $action = strtolower($this->getRequest()->getActionName());
        // Allow current actions only
        $openActions = array(
            'checkemail',
            'checktoken',
        );
        $pattern = '/^(' . implode('|', $openActions) . ')/i';

        if (!preg_match($pattern, $action)) {
            if (!$this->_getSession()->authenticate($this)) {
                $this->setFlag('', 'no-dispatch', true);
            }
        } else {
            $this->_getSession()->setNoReferer(true);
        }
    }

    public function checkemailAction()
    {
        $redirect_url = $this->getDefaultRedirect();

        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect($redirect_url);
            return;
        }
        $session = $this->_getSession();

        if ($this->getRequest()->isPost()) {
            $email = trim($this->getRequest()->getPost('email'));
            if (!empty($email)) {
                $customer = $this->helper()->getCustomerByEmail($email);

                if (!$customer->getId()) {
                    $session->addError($this->__('Email does not exist.'));
                    $this->_redirect($redirect_url);
                    return;
                }
                if (!$customer->getIsActive()) {
                    $session->addError($this->__('Accaunt is inactive.'));
                    $this->_redirect($redirect_url);
                    return;
                }

                $token = md5($customer->getEmail() . time() . rand());

                $emailauth = Mage::getModel('web4proemailauth/emailauth');
                $emailauth->setData(array(
                    'token' => $token,
                    'customer_id' => $customer->getId(),
                    'expiration' => time() + ($this->helper()->getConfig('tokenlifetime') * 60),
                ));
                $emailauth->save();


                $mailInfo = array(
                    'email' => $email,
                    'fullname' => $customer->getFirstname() . ' ' . $customer->getLastname(),
                );
                $bodyData = array(
                    'token' => $token,
                    'tokenlifetime' => $this->helper()->getConfig('tokenlifetime'),
                );
                $this->helper()->sendEmail($mailInfo, $bodyData);

                $session->addSuccess($this->__("Email with Sign In link was sent to `{$email}`."));
                $this->_redirect($redirect_url);

            } else {
                $session->addError($this->__('Email is required.'));
                $this->_redirect($redirect_url);
                return;
            }
        }
    }

    public function checktokenAction()
    {
        $redirect_url = $this->getDefaultRedirect();
        $session = $this->_getSession();
        $token = $this->getRequest()->getParam('token');

        if (empty($token)) {
            $session->addError($this->__('Token is empty'));
            $this->_redirect($redirect_url);
            return;
        }

        $emailauth = Mage::getModel('web4proemailauth/emailauth');
        $emailauth->load($token, 'token');

        if (!$emailauth->getId()) {
            $session->addError($this->__('Token was not found'));
            $this->_redirect($redirect_url);
            return;
        }
        if (time() > strtotime($emailauth->getExpiration())) {
            $session->addError($this->__('Login link is expired'));
            $this->_redirect($redirect_url);
            return;
        }

        $customer = $this->helper()->getCustomerById($emailauth->getCustomerId());
        $email = $customer->getEmail();

        try {
            $session->setCustomerAsLoggedIn($customer);
            if ($session->getCustomer()->getIsJustConfirmed()) {
                $this->_welcomeCustomer($session->getCustomer(), true);
            }
        } catch (Mage_Core_Exception $e) {
            switch ($e->getCode()) {
                case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                    $value = $this->helper()->getEmailConfirmationUrl($email);
                    $message = $this->helper()->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                    break;
                case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                    $message = $e->getMessage();
                    break;
                default:
                    $message = $e->getMessage();
            }
            $session->addError($message);
            $session->setUsername($email);
        } catch (Exception $e) {
            // Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
        }

        $this->_loginPostRedirect();
    }


    private function helper()
    {
        return Mage::helper('web4proemailauth');
    }

    private function getDefaultRedirect()
    {
        return 'customer/account/login';
    }

}
