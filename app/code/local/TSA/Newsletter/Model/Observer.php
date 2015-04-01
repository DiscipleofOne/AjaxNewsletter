<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Newsletter
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Newsletter module observer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class TSA_Newsletter_Model_Observer
{
    public function subscribeNewsletterAjax($observer)
    {

        /** @var Mage_Newsletter_SubscriberController $controller */
        $controller = $observer->getControllerAction();
        if($controller->getRequest()->isAjax()){
            $controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
            $this->_handleRequest($controller);
        }
        return;
    }

    protected function _handleRequest($controller)
    {

        $response = array();

        if ($controller->getRequest()->isPost() && $controller->getRequest()->getPost('email')) {
            $customerSession = Mage::getSingleton('customer/session');
            $email = (string)$controller->getRequest()->getPost('email');

            try {
                if (!Zend_Validate::is($email, 'EmailAddress')) {
                    Mage::throwException($controller->__('Please enter a valid email address.'));
                }

                if (Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1 &&
                    !$customerSession->isLoggedIn()
                ) {
                    Mage::throwException($controller->__('Sorry, but administrator denied subscription for guests. Please <a href="%s">register</a>.', Mage::helper('customer')->getRegisterUrl()));
                }

                $ownerId = Mage::getModel('customer/customer')
                    ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                    ->loadByEmail($email)
                    ->getId();
                if ($ownerId !== null && $ownerId != $customerSession->getId()) {
                    Mage::throwException($controller->__('This email address is already assigned to another user.'));
                }

                $status = Mage::getModel('newsletter/subscriber')->subscribe($email);
                if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
                    $response['message'] = $controller->__('Confirmation request has been sent.');
                } else {
                    $response['message'] = $controller->__('Thank you for your subscription.');

                }
            } catch (Mage_Core_Exception $e) {
                $response['message'] = $controller->__('There was a problem with the subscription: %s', $e->getMessage());
            } catch (Exception $e) {
                $response['message'] = $controller->__('There was a problem with the subscription.');
            }
        }
        $controller->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }
}
