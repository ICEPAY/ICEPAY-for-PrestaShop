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

class CreateRefundResponse
{
    /** Refund key, e.g. "pr-..." */
    public string $key;

    /** Refund status, e.g. "pending" | "completed" */
    public string $status;

    /** Amount as returned by API: ['value' => int, 'currency' => string] */
    public array $amount = [];

    /** Refund description/reference from API */
    public string $description;
    public string $reference;

    /** May be null until processed */
    public ?string $refundedAt = null;

    /**
     * Nested original payment payload (as-is from API).
     * Example keys: key, status, amount, paymentMethod, description, reference,
     * webhookUrl, redirectUrl, merchant, isTest, createdAt, expiresAt, updatedAt,
     * meta, links
     */
    public array $payment = [];

    /** ISO8601 strings from API */
    public string $createdAt;
    public string $updatedAt;

    // --- Convenience getters ---

    public function getRefundKey(): string
    {
        return $this->key;
    }

    public function getRefundAmountValue(): ?int
    {
        return $this->amount['value'] ?? null;
    }

    public function getRefundCurrency(): ?string
    {
        return $this->amount['currency'] ?? null;
    }

    public function getPaymentKey(): ?string
    {
        return $this->payment['key'] ?? null;
    }

    public function getPaymentReference(): ?string
    {
        return $this->payment['reference'] ?? null;
    }

    public function getPaymentIdCart(): ?int
    {
        return $this->payment['meta']['prestashop']['id_cart'] ?? null;
    }

    public function getPaymentCheckoutUrl(): ?string
    {
        return $this->payment['links']['checkout'] ?? null;
    }
}
