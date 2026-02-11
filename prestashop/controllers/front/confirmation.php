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

class ZenocpgConfirmationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if (isset($_GET['cart_id'])) {
            $id_cart = $_GET['cart_id'];

            $query_find = 'SELECT id_zeno_payment  FROM `' . _DB_PREFIX_ . _ZENO_DB_TABLE_ . '` WHERE id_cart = ' . $id_cart;
            $cart_ids = Db::getInstance()->getValue($query_find);

            if ($cart_ids != '') {
                $headers = [
                    'Content-Type: application/json',
                    'Accept: application/json',
                ];

                $zeno_api_url = ZCPG_API_ENDPOINT . '/api/v1/checkouts/' . $cart_ids;

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_URL, $zeno_api_url);
                curl_setopt($ch, CURLOPT_POST, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_FAILONERROR, true);
                curl_setopt($ch, CURLINFO_HEADER_OUT, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);

                // Check for errors
                if (curl_errno($ch)) {
                    echo 'Error:' . curl_error($ch);
                    echo 'Error connecting with the gateway';
                }
                curl_close($ch);

                $body = json_decode($response, true);
                $payment_url = isset($body['checkoutUrl']) ? (string) $body['checkoutUrl'] : '';
                $order_status_back = isset($body['status']) ? (string) $body['status'] : '';
                $id_zeno_payment = isset($body['id']) ? (string) $body['id'] : '';

                $cart = new Cart((int) $id_cart);
                $id_currency = $cart->id_currency;
                $amount = $cart->getOrderTotal(true, Cart::BOTH);
                $id_customer = $cart->id_customer;
                $customer = new Customer($id_customer);
                $secure_key = $customer->secure_key;
                $payment_status = (int) Configuration::getGlobalValue('ZENO_WAITING_PAYMENT');

                $this->module->validateOrder(
                    (int) $id_cart,
                    $payment_status,
                    (float) $amount,
                    $this->module->displayName,
                    null,
                    [],
                    (int) $id_currency,
                    false,
                    $secure_key);

                $id_order = Order::getIdByCartId((int) $id_cart);

                $pr_order_status_complete = (int) Configuration::get('ZENO_PAYMENT_ACCEPTED');
                if (!$pr_order_status_complete) {
                    $pr_order_status_complete = (int) Configuration::get('PS_OS_PAYMENT');
                }

                if ($order_status_back == 'COMPLETED') {
                    $this->order_complete_status((int) $id_order, $pr_order_status_complete);
                }
            }
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
