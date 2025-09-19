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

class CreateRefundRequest
{
    /** Required: max 255 chars */
    public string $reference;

    /** Optional: max 255 chars */
    public ?string $description = null;

    /**
     * Required: integer in minor units (> 0)
     * e.g. 299 means €2.99
     */
    public int $amountValue;

    /**
     * required: Transaction that has to be refunded
     *
     * @var string
     */
    public string $id_transaction;

    public function __construct(string $id_transaction, string $reference, int $amountValue, string $description = null)
    {
        $this->id_transaction = $id_transaction;
        $this->reference = $reference;
        $this->amountValue = $amountValue;
        $this->description = $description;
        $this->assertValid();
    }

    public function getIdTransaction(): string
    {
        return $this->id_transaction;
    }

    /**
     * Build payload for the refunds API.
     * Note: currency is omitted; gateway uses the payment's currency.
     */
    public function toArray(): array
    {
        $this->assertValid();

        $data = [
            'reference' => $this->reference,
            'amount' => [
                'value' => $this->amountValue,
            ],
        ];

        if ($this->description !== null && $this->description !== '') {
            $data['description'] = $this->description;
        }

        return $data;
    }

    private function assertValid(): void
    {
        if ($this->reference === '' || mb_strlen($this->reference) > 255) {
            throw new \InvalidArgumentException('reference must be 1..255 characters.');
        }

        if ($this->description !== null && mb_strlen($this->description) > 255) {
            throw new \InvalidArgumentException('description must be <= 255 characters.');
        }

        if ($this->amountValue <= 0) {
            throw new \InvalidArgumentException('amount.value must be an integer > 0 (minor units).');
        }
    }
}
