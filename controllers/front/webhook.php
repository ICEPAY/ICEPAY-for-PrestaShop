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

use Icepay\Icepay\Repository\icepayRefund;
use Icepay\Icepay\Repository\icepayTransaction;
use Icepay\Icepay\Service\ConfigService;
use Icepay\Icepay\Service\IcepayStatus;

class IcepayWebhookModuleFrontController extends ModuleFrontController
{
    /**
     * @return void
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * Process transaction accordingly. ValidateOrder() on successful transations,
     * update transaction status in db if not.
     */
    public function postProcess()
    {
        $configService = new ConfigService();

        // Get raw POST body and decode
        $rawData = file_get_contents('php://input');
        $decodedData = json_decode($rawData, true);

        // Get headers and signature
        $headers = array_change_key_case($this->getAllHeaders() ?: []);

        $secret = $configService->getMerchantSecret();
        $computedSignature = base64_encode(hash_hmac('sha256', $rawData, $secret, true));
        $receivedSignature = ($headers['icepay-signature'] ?? null);

        // Check signature validity
        if ($computedSignature !== $receivedSignature) {
            PrestaShopLogger::addLog('[ICEPAY][WEBHOOK] Invalid signature');

            header('HTTP/1.1 500 Invalid signature');
            exit;
        }

        if (Tools::getValue('id_cart') != $decodedData['meta']['prestashop']['id_cart']) {
            PrestaShopLogger::addLog('[ICEPAY][WEBHOOK] carts dont match ' . Tools::getValue('id_cart') . ' != ' . $decodedData['meta']['prestashop']['id_cart']);

            header('HTTP/1.1 500 Invalid cart');
            exit;
        }

        if ($decodedData['refunds'] && is_array($decodedData['refunds'])) {
            $this->handleRefund($decodedData);
        }

        // Handle order creation as usual

        $cart = new Cart($decodedData['meta']['prestashop']['id_cart']);

        // Another safety
        if (!strpos($decodedData['webhookUrl'], (string) $cart->id)) {
            header('HTTP/1.1 500 possible forgery');
            PrestaShopLogger::addLog('[ICEPAY][WEBHOOK] Possible forgery: ' . $cart->id);

            exit;
        }

        switch ($decodedData['status']) {
            case IcepayStatus::STATUS_COMPLETED:
                try {
                    // Validate the order.
                    $this->module->validateOrder(
                        (int) $cart->id,
                        (int) Configuration::get('PS_OS_PAYMENT'),
                        (float) $decodedData['amount']['value'] / 100,
                        $decodedData['paymentMethod']['type'],
                        ''
                    );
                } catch (PrestaShopException $e) {
                    // Log error or handle failure
                    header('HTTP/1.1 500 ValidateOrder failed');

                    PrestaShopLogger::addLog('[ICEPAY][WEBHOOK] Order validation failed: ' . $e->getMessage(), 3);
                    exit;
                }

                // Get the newly created order
                $order = new Order($this->module->currentOrder);

                // Get the existing payment (should be only one just added)
                $payments = $order->getOrderPayments();

                if (!empty($payments)) {
                    $orderPayment = $payments[0]; // Assuming only one payment per order
                    $orderPayment->transaction_id = $decodedData['key'];
                    $orderPayment->update(); // Save the updated transaction ID
                }

                // Update in own tables
                (new icepayTransaction())->updatePayment($decodedData['key'], ['status' => $decodedData['status']]);

                header('HTTP/1.1 200');
                exit('1');

            case IcepayStatus::STATUS_EXPIRED:
            case IcepayStatus::STATUS_PENDING:
                (new icepayTransaction())->updatePayment($decodedData['key'], ['status' => $decodedData['status']]);
                // Do nothing except updating transaction status.
                header('HTTP/1.1 200');
                break;
        }
        exit;
    }

    /**
     * @return false|array
     *
     * Fetch all the headers and cleanup the keys from webhook
     */
    protected function getAllHeaders(): false|array
    {
        if (!function_exists('getallheaders')) {
            $headers = [];

            foreach ($_SERVER as $name => $value) {
                if (str_starts_with($name, 'HTTP_')) {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }

            return $headers;
        }

        return getallheaders();
    }

    /**
     * @param array $decodedData
     *
     * @return void
     *
     * Simply update the status for now
     */
    protected function handleRefund(array $decodedData)
    {
        foreach ($decodedData['refunds'] as $refund) {
            (new icepayRefund())->updateRefund($refund['key'], ['status' => $refund['status']]);
        }

        header('HTTP/1.1 200');
        exit('1');
    }
}
