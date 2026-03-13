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

class ConfigService
{
    public const PAYMENT_METHOD_SETTINGS_KEY = 'ICEPAY_PAYMENT_METHOD_SETTINGS';

    public function getMerchantId(): string
    {
        return \Configuration::get('ICEPAY_MERCHANT_ID');
    }

    public function getMerchantSecret(): string
    {
        return \Configuration::get('ICEPAY_MERCHANT_SECRET');
    }

    public function getPaymentMethodSettings(): array
    {
        $rawSettings = \Configuration::get(self::PAYMENT_METHOD_SETTINGS_KEY);
        if (!is_string($rawSettings) || '' === $rawSettings) {
            return $this->getDefaultPaymentMethodSettings();
        }

        $decodedSettings = json_decode($rawSettings, true);
        if (!is_array($decodedSettings)) {
            return $this->getDefaultPaymentMethodSettings();
        }

        return $this->normalizePaymentMethodSettings($decodedSettings);
    }

    public function savePaymentMethodSettings(array $settings): void
    {
        \Configuration::updateValue(
            self::PAYMENT_METHOD_SETTINGS_KEY,
            json_encode($this->normalizePaymentMethodSettings($settings))
        );
    }

    public function getMethodSettings(string $methodId): array
    {
        $settings = $this->getPaymentMethodSettings();

        return $settings['methods'][$methodId] ?? ['countries' => []];
    }

    public function isMethodAvailableForCountry(string $methodId, ?string $countryIso): bool
    {
        $methodSettings = $this->getMethodSettings($methodId);
        $countries = $methodSettings['countries'] ?? [];

        if (empty($countries)) {
            return true;
        }

        if (null === $countryIso || '' === $countryIso) {
            return false;
        }

        return in_array(strtoupper($countryIso), $countries, true);
    }

    private function getDefaultPaymentMethodSettings(): array
    {
        return [
            'version' => 1,
            'methods' => [],
        ];
    }

    private function normalizePaymentMethodSettings(array $settings): array
    {
        $normalizedSettings = $this->getDefaultPaymentMethodSettings();
        $normalizedSettings['version'] = isset($settings['version']) ? (int) $settings['version'] : 1;

        $methods = $settings['methods'] ?? [];
        if (!is_array($methods)) {
            return $normalizedSettings;
        }

        foreach ($methods as $methodId => $methodSettings) {
            if (!is_string($methodId) || '' === $methodId || !is_array($methodSettings)) {
                continue;
            }

            $countries = $methodSettings['countries'] ?? [];
            if (!is_array($countries)) {
                $countries = [];
            }

            $normalizedCountries = [];
            foreach ($countries as $countryIso) {
                if (!is_string($countryIso)) {
                    continue;
                }

                $countryIso = strtoupper(trim($countryIso));
                if ('' === $countryIso) {
                    continue;
                }

                $normalizedCountries[$countryIso] = $countryIso;
            }

            ksort($normalizedCountries);

            $normalizedSettings['methods'][$methodId] = [
                'countries' => array_values($normalizedCountries),
            ];
        }

        ksort($normalizedSettings['methods']);

        return $normalizedSettings;
    }
}
