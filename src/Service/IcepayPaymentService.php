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

namespace Icepay\Icepay\Service;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Icepay\Icepay\Api\IcepayClient;
use Icepay\Icepay\Dto\CreateCheckoutRequest;
use Icepay\Icepay\Dto\CreateCheckoutResponse;
use Icepay\Icepay\Dto\CreateRefundRequest;
use Icepay\Icepay\Dto\CreateRefundResponse;
use Icepay\Icepay\Dto\PaymentMethodDto;
use Icepay\Icepay\Dto\RetrievePaymentDto;
use Icepay\Icepay\Exception\IcepayException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class IcepayPaymentService
{
    private IcepayClient $client;
    private SerializerInterface $serializer;

    public function __construct()
    {
        $this->client = new IcepayClient();

        $this->serializer = new Serializer(
            [new ObjectNormalizer(), new ArrayDenormalizer()],
            [new JsonEncoder()]
        );
    }

    /**
     * @return PaymentMethodDto[]
     */
    public function getAvailablePaymentMethods(array $params = []): array
    {
        try {
            $responseData = $this->client->get('payments/methods');

            $methods = $this->serializer->denormalize(
                $responseData,
                PaymentMethodDto::class . '[]'
            );

            foreach ($methods as $method) {
                // Populate local icon

                $formattedName = $method->id;
                $basePath = _PS_MODULE_DIR_ . 'icepay/views/img/icons/';
                $baseUrl = _MODULE_DIR_ . 'icepay/views/img/icons/';
                $extensions = ['svg', 'png', 'jpg'];

                foreach ($extensions as $ext) {
                    $filename = $formattedName . '.' . $ext;
                    if (file_exists($basePath . $filename)) {
                        $method->logo = $baseUrl . $filename;
                        break; // stop at first match
                    }
                }
            }

            return $methods;
        } catch (IcepayException $e) {
            \PrestaShopLogger::addLog('ICEPAY fetch error: ' . $e->getMessage(), 3);

            return [];
        }
    }

    public function createPayment(CreateCheckoutRequest $request): ?CreateCheckoutResponse
    {
        try {
            $responseData = $this->client->post('payments', $request->toArray());

            return $this->serializer->denormalize(
                $responseData,
                CreateCheckoutResponse::class
            );
        } catch (IcepayException $e) {
            \PrestaShopLogger::addLog('ICEPAY payment creation failed: ' . $e->getMessage(), 3);
            \PrestaShopLogger::addLog(var_export($request->toArray(), true), 3);
            \PrestaShopLogger::addLog(var_export($responseData, true), 3);

            return null;
        }
    }

    public function getPaymentForTransaction($key)
    {
        try {
            $responseData = $this->client->get('payments/' . $key);

            return $this->serializer->denormalize(
                $responseData,
                RetrievePaymentDto::class,
                context: [
                    AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => true, // ignore fields you don't model
                ]
            );
        } catch (IcepayException $e) {
            \PrestaShopLogger::addLog('ICEPAY payment retrieval failed: ' . $e->getMessage(), 3);

            return null;
        }
    }

    public function createRefund(CreateRefundRequest $request): CreateRefundResponse|bool
    {
        try {
            $responseData = $this->client->post('payments/' . $request->getIdTransaction() . '/refund', $request->toArray());

            return $this->serializer->denormalize(
                $responseData,
                CreateRefundResponse::class
            );
        } catch (IcepayException $e) {
            \PrestaShopLogger::addLog('ICEPAY refund creation failed: ' . $e->getMessage(), 3);

            return false;
        }
    }
}
