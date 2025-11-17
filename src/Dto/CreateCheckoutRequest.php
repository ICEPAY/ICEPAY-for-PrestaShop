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

namespace Icepay\Icepay\Dto;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CreateCheckoutRequest
{
    public string $reference;

    public int $id_cart;

    public ?string $description = null;

    public ?string $status = null;

    public int $amountValue;
    public string $amountCurrency;

    public ?string $redirectUrl = null;
    public ?string $webhookUrl = null;

    public ?string $customerEmail = null;

    public ?string $paymentMethodType = null; // e.g. 'card', 'ideal', etc.

    public function toArray(): array
    {
        $data = [
            'reference' => $this->reference,
            'description' => $this->description ?? $this->reference,
            'meta' => [
                'prestashop' => [
                    'id_cart' => $this->id_cart,
                ],
                'integration' => [
					'type' => 'prestashop',
	                'version' => '1.0.1',
	                'developer' => 'ICEPAY',
                ],
            ],
            'status' => $this->status,
            'amount' => [
                'value' => $this->amountValue,
                'currency' => strtolower($this->amountCurrency),
            ],
        ];

        if ($this->redirectUrl) {
            $data['redirectUrl'] = $this->redirectUrl;
        }

        if ($this->webhookUrl) {
            $data['webhookUrl'] = $this->webhookUrl;
        }

        if ($this->customerEmail) {
            $data['customer'] = ['email' => $this->customerEmail];
        }

        if ($this->paymentMethodType) {
            $data['paymentMethod'] = ['type' => $this->paymentMethodType];
        }

        return $data;
    }
}
