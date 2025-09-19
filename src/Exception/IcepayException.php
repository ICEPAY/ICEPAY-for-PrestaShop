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

namespace Icepay\Icepay\Exception;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Icepay\Icepay\Service\Logger;

class IcepayException extends \Exception
{
    public function __construct(string $message, int $code = 0, \Exception $previous = null)
    {
        // Log the error using your custom Logger class
        Logger::log('[Icepay] ' . $message, 3); // severity 3 = Error

        // Show error only in dev mode
        if (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_) {
            echo '<pre style="background:#fee;border:1px solid #c00;padding:10px;color:#c00">';
            echo '[Icepay] ' . $message;
            echo '</pre>';
        }

        parent::__construct($message, $code, $previous);
    }
}
