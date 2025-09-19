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

use Icepay\Icepay\Dto\CreateCheckoutRequest;
use Icepay\Icepay\Repository\icepayTransaction;
use Icepay\Icepay\Service\IcepayPaymentService;

if (!defined('_PS_VERSION_')) {
    exit;
}
class IcepayRedirectModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $cart = $this->context->cart;
        $customer = $this->context->customer;
        $link = $this->context->link;

        $method = Tools::getValue('id');

        // Check if payment method is active.
        if (!$this->paymentMethodIsActive($method)) {
            $this->displayError($this->trans('This payment method is no longer active.'));
        }

        // We can savely assume Enum is an active paymentmethod here.
        $enum = $method;

        $icepayPaymentService = new IcepayPaymentService();

        $checkout = new CreateCheckoutRequest();
        $checkout->reference = 'CART-' . (int) $cart->id;
        $checkout->id_cart = $cart->id;
        $checkout->description = $this->createDescription($cart);
        $checkout->amountValue = (int) ($cart->getOrderTotal() * 100);
        $checkout->amountCurrency = Currency::getIsoCodeById($cart->id_currency);
        $checkout->redirectUrl = $link->getModuleLink('icepay', 'return', ['id_cart' => $cart->id]);
        $checkout->webhookUrl = $link->getModuleLink('icepay', 'webhook', ['id_cart' => $cart->id]);
        $checkout->customerEmail = $customer->email;
        $checkout->paymentMethodType = $enum;

        $responseDto = $icepayPaymentService->createPayment($checkout);

        // Create rows in own table.
        $insert = (new icepayTransaction())->insertFromResponse($responseDto);

        if ($responseDto->getDirectUrl()) {
            Tools::redirect($responseDto->getDirectUrl());
            exit;
        } elseif ($responseDto->getCheckoutUrl()) {
            // Checkout link instead of direct.
            Tools::redirect($responseDto->getCheckoutUrl());
            exit;
        }

        throw new Icepay\Icepay\Exception\IcepayException('Failed to redirect to:' . var_export($responseDto, true));
    }

    protected function displayError($message, $description = false)
    {
        /*
         * Create the breadcrumb for your ModuleFrontController.
         */
        $this->context->smarty->assign('path', '
			<a href="' . $this->context->link->getPageLink('order', null, null, 'step=3') . '">' . $this->module->l('Payment') . '</a>
			<span class="navigation-pipe">&gt;</span>' . $this->module->l('Error'));

        /*
         * Set error message and description for the template.
         */
        array_push($this->errors, $this->module->l($message), $description);

        return $this->setTemplate('error.tpl');
    }

    protected function paymentMethodIsActive($method)
    {
        $icepayPaymentService = new IcepayPaymentService();
        foreach ($icepayPaymentService->getAvailablePaymentMethods() as $availablePaymentMethod) {
            if ($method === $availablePaymentMethod->id) {
                return true;
            }
        }

        return false;
    }

    private function createDescription($cart)
    {
        $products = $cart->getProducts();
        $lines = [];

        foreach ($products as $product) {
            $qty = (int) $product['cart_quantity'];
            $name = $product['name'];
            $price = $product['price_wt']; // With tax
            $lines[] = "{$qty}x - {$name} - {$price}";
        }

        $description = implode("\n", $lines);

        // Truncate to 255 characters
        if (strlen($description) > 255) {
            $description = mb_substr($description, 0, 252) . '...';
        }

        return $description;
    }
}
