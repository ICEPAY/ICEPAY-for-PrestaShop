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
use Icepay\Icepay\Dto\CreateCheckoutResponse;

class icepayTransaction
{
    private string $table;

    public function __construct()
    {
        $this->table = 'icepay_transaction';
    }

    public function insertFromResponse(CreateCheckoutResponse $dto): bool
    {
        return \Db::getInstance()->insert($this->table, [
            'key' => pSQL($dto->key),
            'id_cart' => ($dto->getIdCart() ?? null),
            'status' => $dto->status,
            'paymentMethod' => pSQL($dto->paymentMethod),
            'date_add' => date('Y-m-d H:i:s'),
            'date_upd' => date('Y-m-d H:i:s'),
        ]);
    }

    public function updatePayment(string $transactionKey, array $data)
    {
        if (!isset($data['date_upd'])) {
            $data['date_upd'] = date('Y-m-d H:i:s');
        }

        return \Db::getInstance()->update($this->table, $data, '`key` = "' . pSQL($transactionKey) . '"');
    }

    /**
     * Fetch the 5 latest key to display on error
     *
     * @return array|null;
     **/
    public function getTransactionsFromCart(int $id_cart)
    {
        return \Db::getInstance()->executeS('SELECT `key` FROM ' . _DB_PREFIX_ . $this->table . ' WHERE id_cart = ' . $id_cart . ' ORDER BY id_icepay_transaction DESC LIMIT 0,5') ?? null;
    }

    /**
     * Fetch all the payment tries for the backoffice
     *
     * @return array|null;
     **/
    public function getTransactionsByAdmin(int $id_cart)
    {
        // 1. Fetch main transactions
        $transactions = \Db::getInstance()->executeS(
            'SELECT *
     FROM ' . _DB_PREFIX_ . pSQL($this->table) . '
     WHERE id_cart = ' . (int) $id_cart . '
     ORDER BY id_icepay_transaction DESC'
        );

        // 2. Collect all keys
        $keys = array_column($transactions, 'key');

        if (!empty($keys)) {
            $refunds = \Db::getInstance()->executeS(
                'SELECT *
         FROM ' . _DB_PREFIX_ . 'icepay_refund
         WHERE `key` IN ("' . implode('","', array_map('pSQL', $keys)) . '")'
            );

            // 3. Group refunds by transaction key
            $refundMap = [];
            foreach ($refunds as $refund) {
                $refundMap[$refund['key']][] = $refund;
            }

            // 4. Attach refunds to each transaction
            foreach ($transactions as &$tx) {
                $tx['refunds'] = $refundMap[$tx['key']] ?? [];
            }
        }

        return $transactions;
    }

    /**
     * Fetch the latest transaction from database
     *
     * @return array|null;
     **/
    public function getLatestTransactionFromCart(int $id_cart)
    {
        return \Db::getInstance()->getRow('SELECT * FROM ' . _DB_PREFIX_ . $this->table . ' WHERE id_cart = ' . $id_cart . ' ORDER BY id_icepay_transaction DESC') ?? null;
    }
}
