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

namespace Icepay\Icepay\Http;

if (!defined('_PS_VERSION_')) {
    exit;
}
use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\HttpClient\HttpClient;

class HttpClientFactory
{
    public static function create(): HttpClientInterface
    {
        if (version_compare(_PS_VERSION_, '1.7.7', '>=')) {
            return new SymfonyHttpClient(HttpClient::create());
        } else {
            if (!class_exists(GuzzleClient::class)) {
                throw new \RuntimeException('Icepay Error:GuzzleClient was not found.');
            }

            return new GuzzleHttpClient(new GuzzleClient());
        }
    }
}
