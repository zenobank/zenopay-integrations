<?php
/**
 * 2007-2026 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2026 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class ZenocpgWebhookModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if (isset($_POST['data'])) {
            $data = $_POST['data'];
            $id_zeno_payment = $data['id'];
            $id_cart = $data['orderId'];
            $order_status_back = $data['status'];
            $verification_token_back = $data['verificationToken'];
            $checkout_url = $data['checkoutUrl'];

            $sql = 'SELECT id_cart FROM ' . _DB_PREFIX_ . 'cart WHERE id_cart = "' . $id_cart . '"';
            $id_cart = Db::getInstance()->getValue($sql);
            // Validate that order exists
            if (!$id_cart) {
                exit;
            }

            $cart = new Cart((int) $id_cart);
            $id_customer = $cart->id_customer;
            $customer = new Customer($id_customer);
            $secure_key = $customer->secure_key;
            $verification_token = hash_hmac('sha256', (string) $id_cart, $secure_key);

            $id_order = Order::getIdByCartId((int) $id_cart);

            $pr_order_status_complete = (int) Configuration::get('ZENO_PAYMENT_ACCEPTED');
            if (!$pr_order_status_complete) {
                $pr_order_status_complete = (int) Configuration::get('PS_OS_PAYMENT');
            }

            if ($verification_token == $verification_token_back && $order_status_back == 'COMPLETED') {
                $this->order_complete_status((int) $id_order, $pr_order_status_complete);
            }
        } else {
            echo 'Webhook request error';
            exit;
        }
    }

    public function order_complete_status($id_order, $pr_order_status_complete)
    {
        $order = new Order((int) $id_order);
        if (!Validate::isLoadedObject($order)) {
            return false;
        }

        $new_history = new OrderHistory();
        $new_history->id_order = (int) $id_order;
        $result = $new_history->changeIdOrderState((int) $pr_order_status_complete, (int) $id_order, true);
        $new_history->add();
        if (!$result) {
            return false;
        }
        /*
        // Synchronize stock if advanced stock management is enabled
        if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            foreach ($order->getProducts() as $product) {
                if (StockAvailable::dependsOnStock($product['product_id'])) {
                    StockAvailable::synchronize($product['product_id'], (int)$order->id_shop);
                }
            }
        }*/

        return true;
    }
}
