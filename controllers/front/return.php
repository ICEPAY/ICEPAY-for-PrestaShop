<?php
/**
 * 2025 Channel-support BV
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 * You may not use this file except in compliance with the License.
 *
 * @author    Channel Support <info@channel-support.nl>
 * @copyright 2025 Channel-support BV
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

use Icepay\Icepay\Service\IcepayStatus;

class IcepayReturnModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $respository = new Icepay\Icepay\Repository\icepayTransaction();

        if (Tools::isSubmit('getStatusForCart') && Tools::isSubmit('failed')) {
            // If transaction did not succeed in time.
            $this->context->controller->errors[] = $this->trans('An error occurred fetching the payment status!');

            $cart = new Cart(Tools::getValue('id_cart'));
            $customer = $this->context->customer;

            if ($cart->id_customer != $customer->id) {
                // Don't display info because this customer might not be the actual payer
                return;
            }

            switch ($state = $respository->getLatestTransactionFromCart($cart->id)['status']) {
                case IcepayStatus::STATUS_EXPIRED:
                    // Definite status. Let customer try again.
                    Tools::redirect($this->context->link->getPageLink('order', null, null, 'step=3&icepayExpired=true'));
                    exit;

                default:
                case IcepayStatus::STATUS_PENDING:
                case IcepayStatus::STATUS_STARTED:
                    $transactions = $respository->getTransactionsFromCart($cart->id);

                    // Might be multiple.
                    $keys = '';
                    foreach ($transactions as $transaction) {
                        $keys .= $transaction['key'] . ' - ';
                    }

                    $keys = rtrim($keys, ' -');

                    $this->context->smarty->assign([
                        'icepayTransaction' => $keys,
                        'state' => $state,
                        'try_again_link' => $this->context->link->getPageLink('order', null, null, 'step=3'),
                    ]);
                    break;
            }

            $transactions = $respository->getTransactionsFromCart($cart->id);

            // Might be multiple.
            $keys = '';
            foreach ($transactions as $transaction) {
                $keys .= $transaction['key'] . ' - ';
            }

            $keys = rtrim($keys, ' -');

            $this->context->smarty->assign([
                'icepayTransaction' => $keys,
            ]);
        }

        if (Tools::isSubmit('getStatusForCart') && !Tools::isSubmit('failed')) {
            $cart = new Cart(Tools::getValue('id_cart'));
            $customer = $this->context->customer;

            if ($cart->id_customer != $customer->id) {
                exit(json_encode([
                    'success' => false,
                ]));
            }

            $order = Order::getByCartId((int) $cart->id);

            if (Validate::isLoadedObject($order)) {
                // Order was created by webhook!
                $params = [
                    'id_cart' => (int) $cart->id,
                    'id_module' => (int) $this->module->id,
                    'id_order' => (int) $order->id,
                    'key' => $customer->secure_key,
                ];

                $confirmationLink = $this->context->link->getPageLink('order-confirmation', true, null, http_build_query($params));

                exit(json_encode([
                    'href' => $confirmationLink,
                    'success' => 1,
                ]));
            }

            exit(json_encode([
                'success' => false,
            ]));
        }

        parent::postProcess();
    }

    public function setMedia()
    {
        Media::addJsDef([
            'fetchStatusUrl' => $this->getCurrentURL() . '&getStatusForCart=1',
        ]);
        $this->context->controller->registerJavascript(
            'icepay_return',
            '/modules/icepay/views/js/return.js'
        );

        return parent::setMedia();
    }

    public function initContent()
    {
        parent::initContent();

        $this->setTemplate('module:icepay/views/templates/front/return.tpl');
    }
}
