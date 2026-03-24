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

use Icepay\Icepay\Dto\PaymentMethodDto;
use Icepay\Icepay\Repository\icepayTransaction;
use Icepay\Icepay\Service\ConfigService;
use Icepay\Icepay\Service\IcepayPaymentService;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

class Icepay extends PaymentModule
{
    public function __construct()
    {
        $this->name = 'icepay';
        $this->tab = 'payments_gateways';
        $this->version = '1.2.0';
        $this->author = 'Channel-support';
        $this->need_instance = 1;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('ICEPAY integration');
        $this->description = $this->l('Handle your payments ICEPAY payments with this module');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
    }

    /**
     * Install Prestashop module
     */
    public function install(): bool
    {
        if (!extension_loaded('curl')) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');

            return false;
        }

        include dirname(__FILE__) . '/sql/install.php';

        return parent::install()
            && $this->registerHook('actionFrontControllerSetMedia')
            && $this->registerHook('actionAdminControllerSetMedia')
            && $this->registerHook('paymentOptions')
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayAdminOrder')
            && $this->_installTab();
    }

    /**
     * Install Prestashop tab to get the ajax controller
     */
    private function _installTab(): bool
    {
        // Create a hidden tab that maps the class name to your module
        $tab = new Tab();
        $tab->active = false; // hidden in BO menu
        $tab->class_name = 'AdminIcepayAjax';
        $tab->module = $this->name;
        $tab->id_parent = (int) 1;
        foreach (Language::getLanguages(false) as $lang) {
            $tab->name[$lang['id_lang']] = 'Icepay Ajax';
        }
        if (!$tab->add()) {
            return false;
        }

        return true;
    }

    public function uninstall(): bool
    {
        include dirname(__FILE__) . '/sql/uninstall.php';

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     *
     * @throws SmartyException
     */
    public function getContent(): string
    {
        /*
         * If values have been submitted in the form, process.
         */
        if (Tools::isSubmit('submitIcepayModule')) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = '';

        $config = $this->getConfigFormValues();
        if ($config['ICEPAY_MERCHANT_ID'] && $config['ICEPAY_MERCHANT_SECRET']) {
            $service = $this->get('icepay.service.icepay_payment');
            $configService = $this->get('icepay.service.configuration');
            $availableMethods = $service->getAvailablePaymentMethods();
            $methodSettings = $configService->getPaymentMethodSettings()['methods'];

            foreach ($availableMethods as $method) {
                $method->adminLabel = $this->getTranslatedDescription($method);
            }

            $this->context->smarty->assign([
                'available_methods' => $availableMethods,
                'country_options' => $this->getCountryOptions(),
                'method_country_lookup' => $this->buildMethodCountryLookup($methodSettings),
                'method_settings_form_action' => $this->getModuleConfigAction(),
                'method_settings_token' => Tools::getAdminTokenLite('AdminModules'),
            ]);

            $output .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/listPaymentMethods.tpl');
        }

        return $this->renderForm() . $output;
    }

    /**
     * Form for Icepay connection details
     */
    protected function renderForm(): string
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitIcepayModule';
        $helper->currentIndex = $this->getModuleConfigAction();
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getConfigForm()]);
    }

    /**
     * Structure of the form
     */
    protected function getConfigForm(): array
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('ICEPAY Portal > Merchants > View Merchant.'),
                        'name' => 'ICEPAY_MERCHANT_ID',
                        'label' => $this->l('Merchant ID'),
                    ],
                    [
                        'type' => 'password',
                        'name' => 'ICEPAY_MERCHANT_SECRET',
                        'label' => $this->l('Merchant Secret'),
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues(): array
    {
        return [
            'ICEPAY_MERCHANT_ID' => Configuration::get('ICEPAY_MERCHANT_ID'),
            'ICEPAY_MERCHANT_SECRET' => Configuration::get('ICEPAY_MERCHANT_SECRET'),
        ];
    }

    /**
     * Save form data.
     */
    protected function postProcess(): void
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            if (Tools::getIsset($key)) {
                $value = Tools::getValue($key);

                if ('ICEPAY_MERCHANT_SECRET' === $key && '' === trim((string) $value)) {
                    continue;
                }

                Configuration::updateValue($key, $value);
            }
        }
    }

    public function hookActionFrontControllerSetMedia(): void
    {
        $controller = $this->context->controller;

        if ($controller && $controller->php_self === 'order') {
            $controller->registerStylesheet(
                'module-icepay-checkout',
                'modules/' . $this->name . '/views/css/front.css',
                ['position' => 'bottom', 'priority' => 150]
            );
        }
    }

    public function hookDisplayHeader(): void
    {
        if (Tools::getValue('icepayExpired') === 'true') {
            $this->context->controller->errors[] = $this->trans('Your payment has expired. Please try again.');
        }
    }

    /**
     * Return payment options available for PS 1.7+
     *
     * @param array $params Hook parameters
     *
     * @return array|void|null
     */
    public function hookPaymentOptions(array $params)
    {
        if (!$this->active) {
            return;
        }
        if (!$this->checkCurrency($params['cart'])) {
            return;
        }

        $service = new IcepayPaymentService();
        $configService = new ConfigService();
        $countryIso = $this->getCheckoutCountryIso($params['cart']);

        $options = [];
        foreach ($service->getAvailablePaymentMethods() as $method) {
            if (!$configService->isMethodAvailableForCountry($method->id, $countryIso)) {
                continue;
            }

            $option = new PaymentOption();

            $callToActionText = $this->getTranslatedDescription($method);

            $options[] = $option->setCallToActionText($callToActionText)
                ->setAction($this->context->link->getModuleLink($this->name, 'redirect', ['id' => $method->id], true))
                ->setLogo($method->logo)
            ;
        }

        return $options;
    }

    public function checkCurrency($cart): bool
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);
        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return void
     */
    public function hookActionAdminControllerSetMedia()
    {
        $currentController = Tools::getValue('controller');

        if ('AdminOrders' === $currentController) {
            Media::addJsDef([
                'icepayAjaxUrl' => $this->context->link->getAdminLink('AdminIcepayAjax', true, [], ['ajax' => 1]),
            ]);

            $this->context->controller->addJS($this->getPathUri() . 'views/js/back.js');
        }

        if ('AdminModules' === $currentController && Tools::getValue('configure') === $this->name) {
            Media::addJsDef([
                'icepayAjaxUrl' => $this->context->link->getAdminLink('AdminIcepayAjax', true, [], ['ajax' => 1]),
                'icepayConfigMessages' => [
                    'saveSuccess' => $this->l('Payment method configuration saved.'),
                    'saveError' => $this->l('Saving payment method configuration failed.'),
                    'saveInProgress' => $this->l('Saving...'),
                ],
            ]);

            $this->context->controller->addJS($this->getPathUri() . 'views/js/configuration.js');
        }
    }

    /**
     * @return string
     *
     * Display order info block on orders backoffice
     */
    public function hookDisplayAdminOrder($params): string
    {
        $order = new Order($params['id_order']);

        if ($order->module !== $this->name) {
            return '';
        }

        $repository = new icepayTransaction();

        $rows = $repository->getTransactionsByAdmin($order->id_cart);

        $this->smarty->assign([
            'payment_rows' => $rows,
        ]);

        return $this->display($this->getLocalPath(), 'views/templates/hook/order_admin.tpl');
    }

    private function getTranslatedDescription(PaymentMethodDto $method): string
    {
        return match ($method->id) {
            'ideal' => $this->l('iDEAL | Wero'),
            'paypal' => $this->l('PayPal'),
            'card' => $this->l('Card'),
            'bancontact' => $this->l('Bancontact'),
            'eps' => $this->l('EPS'),
            'onlineueberweisen' => $this->l('Online Überweisen'),
            'paybybank' => $this->l('Pay by Bank'),
            default => $method->description,
        };
    }

    private function getModuleConfigAction(): string
    {
        return $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
    }

    private function getCountryOptions(): array
    {
        $countries = Country::getCountries((int) $this->context->language->id, true);
        $options = [];

        foreach ($countries as $country) {
            if (empty($country['iso_code']) || empty($country['name'])) {
                continue;
            }

            $options[] = [
                'iso_code' => strtoupper((string) $country['iso_code']),
                'name' => (string) $country['name'],
            ];
        }

        usort($options, static function (array $left, array $right): int {
            return strcasecmp($left['name'], $right['name']);
        });

        return $options;
    }

    private function buildMethodCountryLookup(array $methodSettings): array
    {
        $lookup = [];

        foreach ($methodSettings as $methodId => $settings) {
            if (!is_array($settings) || !isset($settings['countries']) || !is_array($settings['countries'])) {
                continue;
            }

            foreach ($settings['countries'] as $countryIso) {
                if (!is_string($countryIso) || '' === $countryIso) {
                    continue;
                }

                $lookup[$methodId][strtoupper($countryIso)] = true;
            }
        }

        return $lookup;
    }

    private function getCheckoutCountryIso(?Cart $cart): ?string
    {
        if (!$cart instanceof Cart) {
            return null;
        }

        $addressIds = [
            (int) $cart->id_address_delivery,
            (int) $cart->id_address_invoice,
        ];

        foreach ($addressIds as $addressId) {
            if ($addressId <= 0) {
                continue;
            }

            $address = new Address($addressId);
            if (!Validate::isLoadedObject($address) || empty($address->id_country)) {
                continue;
            }

            $country = new Country((int) $address->id_country);
            if (Validate::isLoadedObject($country) && !empty($country->iso_code)) {
                return strtoupper((string) $country->iso_code);
            }
        }

        if (isset($this->context->country) && Validate::isLoadedObject($this->context->country) && !empty($this->context->country->iso_code)) {
            return strtoupper((string) $this->context->country->iso_code);
        }

        return null;
    }
}
