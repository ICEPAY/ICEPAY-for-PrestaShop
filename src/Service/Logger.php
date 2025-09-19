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

class Logger
{
    public static function log($message, $severity = 1, $object_type = null, $object_id = null, $allow_duplicate = false)
    {
        \PrestaShopLogger::addLog(
            $message,
            $severity,        // 1 = Info, 2 = Warning, 3 = Error
            null,             // error code (optional)
            $object_type,
            $object_id,
            $allow_duplicate
        );
    }
}
