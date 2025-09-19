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
class CreateCheckoutResponse
{
    public string $key;
    public string $status;

    public array $amount;

    public ?string $paymentMethod = null;
    public string $description;
    public string $reference;

    public string $webhookUrl;
    public string $redirectUrl;

    public array $merchant;
    public bool $isTest;
    public array $refunds = [];

    public string $createdAt;
    public string $expiresAt;
    public string $updatedAt;

    public array $meta = [];
    public array $links = [];

    public function getCheckoutUrl(): ?string
    {
        return $this->links['checkout'] ?? null;
    }

    public function getDirectUrl(): ?string
    {
        return $this->links['direct'] ?? null;
    }

    public function getIdCart(): ?string
    {
        return $this->meta['prestashop']['id_cart'] ?? null;
    }
}
