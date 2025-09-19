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

use GuzzleHttp\ClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class GuzzleHttpClient implements HttpClientInterface
{
    private ClientInterface $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $response = $this->client->request($method, $url, $options);

        return new class($response) implements ResponseInterface {
            private $response;

            public function __construct($response)
            {
                $this->response = $response;
            }

            public function getStatusCode(): int
            {
                return $this->response->getStatusCode();
            }

            public function getContent(bool $throw = true): string
            {
                return $this->response->getBody()->getContents();
            }

            // Add other required ResponseInterface methods here
            public function getHeaders(bool $throw = true): array
            {
                return $this->response->getHeaders();
            }

            public function toArray(bool $throw = true): array
            {
                $body = $this->getContent($throw);

                return json_decode($body, true) ?? [];
            }

            public function cancel(): void
            {
                // No-op since Guzzle doesn't have a cancel feature
            }

            /**
             * @param string|null $type
             *
             * @return array|mixed|null
             */
            public function getInfo(string $type = null): mixed
            {
                // Limited implementation – return null or fake data if needed
                $info = [
                    'http_code' => $this->response->getStatusCode(),
                ];

                return $type ? ($info[$type] ?? null) : $info;
            }
        };
    }
}
