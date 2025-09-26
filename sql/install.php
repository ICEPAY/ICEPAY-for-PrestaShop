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
$sql = [];

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'icepay_transaction` (
            `id_icepay_transaction` int(11) NOT NULL AUTO_INCREMENT,
            `key` VARCHAR(128) NOT NULL,
            `id_cart` int(12) NOT NULL,
            `status` VARCHAR(128) NOT NULL,
            `paymentMethod` VARCHAR(128) NOT NULL,
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY  (`id_icepay_transaction`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'icepay_refund` (
            `id_icepay_transaction` int(11) NOT NULL AUTO_INCREMENT,
            `key` VARCHAR(128) NOT NULL,
            `key_refund` VARCHAR(128) NOT NULL,
            `status` VARCHAR(128) NOT NULL,
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY  (`id_icepay_transaction`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
