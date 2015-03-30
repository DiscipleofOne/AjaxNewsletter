<?php
///**
// * Magento Enterprise Edition
// *
// * NOTICE OF LICENSE
// *
// * This source file is subject to the Magento Enterprise Edition License
// * that is bundled with this package in the file LICENSE_EE.txt.
// * It is also available through the world-wide-web at this URL:
// * http://www.magentocommerce.com/license/enterprise-edition
// * If you did not receive a copy of the license and are unable to
// * obtain it through the world-wide-web, please send an email
// * to license@magentocommerce.com so we can send you a copy immediately.
// *
// * DISCLAIMER
// *
// * Do not edit or add to this file if you wish to upgrade Magento to newer
// * versions in the future. If you wish to customize Magento for your
// * needs please refer to http://www.magentocommerce.com for more information.
// *
// * @category    Mage
// * @package     Mage_Newsletter
// * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
// * @license     http://www.magentocommerce.com/license/enterprise-edition
// */
//
///**
// * Newsletter subscribe controller
// *
// * @category    Mage
// * @package     Mage_Newsletter
// * @author      Magento Core Team <core@magentocommerce.com>
// */


require_once(MAGE::getModuleDir('controllers','Mage_Newsletter').DS.'SubscriberController.php');

class TSA_Newsletter_SubscriberController extends Mage_Newsletter_SubscriberController{


    public function newAction()
    {

        $configValue = Mage::getStoreConfig('tsa_newsletter/settings/enabled');
        // 0 is disabled
        // 1 is enabled

        // Ajax response array declaration

        if (1 == $configValue) {
            $response = array();


            if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
                $session = Mage::getSingleton('core/session');
                $customerSession = Mage::getSingleton('customer/session');
                $email = (string)$this->getRequest()->getPost('email');

                try {
                    if (!Zend_Validate::is($email, 'EmailAddress')) {
                        Mage::throwException($this->__('Please enter a valid email address.'));
                    }

                    if (Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1 &&
                        !$customerSession->isLoggedIn()
                    ) {
                        Mage::throwException($this->__('Sorry, but administrator denied subscription for guests. Please <a href="%s">register</a>.', Mage::helper('customer')->getRegisterUrl()));
                    }

                    $ownerId = Mage::getModel('customer/customer')
                        ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                        ->loadByEmail($email)
                        ->getId();
                    if ($ownerId !== null && $ownerId != $customerSession->getId()) {
                        Mage::throwException($this->__('This email address is already assigned to another user.'));
                    }

                    $status = Mage::getModel('newsletter/subscriber')->subscribe($email);
                    if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
//                    $session->addSuccess($this->__('Confirmation request has been sent.'));
                        $response['message'] = $this->__('Confirmation request has been sent.');
                    } else {
//                    $session->addSuccess($this->__('Thank you for your subscription.'));
                        $response['message'] = $this->__('Thank you for your subscription.');

                    }
                } catch (Mage_Core_Exception $e) {
//                $session->addException($e, $this->__('There was a problem with the subscription: %s', $e->getMessage()));
                    $response['message'] = $this->__('There was a problem with the subscription: %s', $e->getMessage());
                } catch (Exception $e) {
//                $session->addException($e, $this->__('There was a problem with the subscription.'));
                    $response['message'] = $this->__('There was a problem with the subscription.');
                }
            }

            //Ajax response
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));

//        $this->_redirectReferer(); // Not needed for ajax method
        }
        else
        {
            parent::newAction();
        }
    }
}