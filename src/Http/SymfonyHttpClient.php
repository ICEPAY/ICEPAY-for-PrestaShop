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
use Symfony\Contracts\HttpClient\HttpClientInterface as SymfonyInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SymfonyHttpClient implements HttpClientInterface
{
    private SymfonyInterface $client;

    public function __construct(SymfonyInterface $client)
    {
        $this->client = $client;
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        return $this->client->request($method, $url, $options);
    }
}
