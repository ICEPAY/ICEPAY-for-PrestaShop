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
use Icepay\Icepay\Service\IcepayPaymentService;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

include _PS_MODULE_DIR_.'icepay/vendor/autoload.php';

class Icepay extends PaymentModule
{
    public function __construct()
    {
        $this->name = 'icepay';
        $this->tab = 'payments_gateways';
        $this->version = '1.1.0';
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
            $this->context->smarty->assign([
                'available_methods' => $service->getAvailablePaymentMethods(),
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
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
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
            Configuration::updateValue($key, Tools::getValue($key));
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

        $options = [];
        foreach ($service->getAvailablePaymentMethods() as $method) {
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
}
