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

namespace Icepay\Icepay\Api;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Icepay\Icepay\Exception\IcepayException;
use Icepay\Icepay\Http\HttpClientFactory;
use Icepay\Icepay\Http\HttpClientInterface;
use Icepay\Icepay\Service\ConfigService;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class IcepayClient
{
    private ConfigService $config;
    private HttpClientInterface $client;

    public function __construct()
    {
        $this->config = new ConfigService();
        $this->client = HttpClientFactory::create();
    }

    private function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->config->getMerchantId() . ':' . $this->config->getMerchantSecret()),
        ];
    }

    public function post(string $endpoint, array $data = [], array $headers = []): ?array
    {
        try {
            $response = $this->client->request('POST', $this->buildUrl($endpoint), [
                'headers' => array_merge($this->getHeaders(), $headers),
                'body' => json_encode($data),
            ]);

            return json_decode($response->getContent(), true);
        } catch (
            TransportExceptionInterface|
            ClientExceptionInterface|
            ServerExceptionInterface|
            RedirectionExceptionInterface $e
        ) {
            throw new IcepayException('ICEPAY POST request failed: ' . $e->getMessage());
        }
    }

    public function get(string $endpoint, array $headers = []): ?array
    {
        try {
            $response = $this->client->request('GET', $this->buildUrl($endpoint), [
                'headers' => array_merge($this->getHeaders(), $headers),
            ]);

            return json_decode($response->getContent(), true);
        } catch (
            TransportExceptionInterface|
            ClientExceptionInterface|
            ServerExceptionInterface|
            RedirectionExceptionInterface $e
        ) {
            throw new IcepayException('ICEPAY GET request failed: ' . $e->getMessage());
        }
    }

    private function buildUrl(string $endpoint): string
    {
        return rtrim('https://checkout.icepay.com/api/', '/') . '/' . ltrim($endpoint, '/');
    }
}
