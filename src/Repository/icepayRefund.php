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

namespace Icepay\Icepay\Repository;

if (!defined('_PS_VERSION_')) {
    exit;
}
use Icepay\Icepay\Dto\CreateRefundResponse;

class icepayRefund
{
    private string $table;

    public function __construct()
    {
        $this->table = 'icepay_refund';
    }

    /**
     * @param CreateRefundResponse $dto
     * @return bool
     * @throws \PrestaShopDatabaseException
     */
    public function insertFromResponse(CreateRefundResponse $dto): bool
    {
        return \Db::getInstance()->insert($this->table, [
            'key' => pSQL($dto->getPaymentKey()),
            'key_refund' => pSQL($dto->getRefundKey()),
            'status' => $dto->status,
            'date_add' => date('Y-m-d H:i:s'),
            'date_upd' => date('Y-m-d H:i:s'),
        ]);
    }

    public function updateRefund(string $key_refund, array $data)
    {
        if (!isset($data['date_upd'])) {
            $data['date_upd'] = date('Y-m-d H:i:s');
        }

        return \Db::getInstance()->update($this->table, $data, '`key_refund` = "' . pSQL($key_refund) . '"');
    }
}
