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

use Symfony\Component\Serializer\Attribute\SerializedName;

final class RetrievePaymentDto
{
    public function __construct(
        public string $key,
        public string $status,
        public AmountDto $amount,
        public PaymentMethodDto $paymentMethod,
        public string $description,
        public string $reference,
        #[SerializedName('webhookUrl')]
        public string $webhookUrl,
        #[SerializedName('redirectUrl')]
        public string $redirectUrl,
        public MerchantDto $merchant,
        public bool $isTest,
        /** @var RefundDto[] */
        public array $refunds,
        /** @var ForwardDto[] */
        public array $forwards,
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $expiresAt,
        public \DateTimeImmutable $updatedAt,
        public MetaDto $meta,
        public LinksDto $links,
    ) {
    }
}

final class AmountDto
{
    public function __construct(
        public int $value,
        public string $currency, // "eur" from the API; keep as-is or normalize elsewhere
    ) {
    }
}

final class PaymentMethodDto
{
    public function __construct(
        public string $type, // e.g. "eps"
    ) {
    }
}

final class MerchantDto
{
    public function __construct(
        public int $id,
        public string $name,
    ) {
    }
}

final class RefundDto
{
    // Fill when API provides refund fields
}

final class ForwardDto
{
    // Fill when API provides forward fields
}

final class MetaDto
{
    public function __construct(
        public ?PrestashopMetaDto $prestashop = null,
        public ?CustomerDto $customer = null,
    ) {
    }
}

final class PrestashopMetaDto
{
    public function __construct(
        #[SerializedName('id_cart')]
        public int $idCart,
    ) {
    }
}

final class CustomerDto
{
    public function __construct(
        public string $email,
    ) {
    }
}

final class LinksDto
{
    public function __construct(
        public string $checkout,
        public string $documentation,
    ) {
    }
}
