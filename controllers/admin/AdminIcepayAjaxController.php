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

use Icepay\Icepay\Dto\CreateRefundRequest;
use Icepay\Icepay\Dto\CreateRefundResponse;
use Icepay\Icepay\Repository\icepayRefund;
use Icepay\Icepay\Service\IcepayPaymentService;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminIcepayAjaxController extends ModuleAdminController
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->ajax) {
            exit;
        }
    }

    public function displayAjaxModal()
    {
        $service = new IcepayPaymentService();
        $id_transaction = Tools::getValue('transaction');
        $transaction = $service->getPaymentForTransaction($id_transaction);

        if (!$transaction) {
            exit(json_encode([
                'ok' => false,
                'message' => $this->module->l('Error. Please retry or check icepay.', 'icepay'),
            ]));
        }

        $max_refund = $transaction->amount->value;

        foreach ($transaction->refunds as $refund) {
            if ($refund['status'] == 'completed' || $refund['status'] == 'pending') {
                $max_refund -= $refund['amount']['value'];
            }
        }

        $this->context->smarty->assign([
            'transaction' => $transaction,
            'max_refund' => ($max_refund / 100),
        ]);

        exit(json_encode([
            'ok' => true,
            'summary' => $this->context->smarty->fetch('module:icepay/views/templates/admin/modalReturn.tpl'),
            'max_refund' => ($max_refund / 100),
            'reference' => $transaction->reference,
        ]));
    }

    public function displayAjaxDoRefund()
    {
        $id_transaction = Tools::getValue('transaction');
        $amount = Tools::getValue('amount');
        $reason = Tools::getValue('reason');
        $reference = Tools::getValue('reference');

        try {
            $refund = new CreateRefundRequest(
                $id_transaction,
                'REFUND-' . $reference,
                (int) round($amount * 100, 0, PHP_ROUND_HALF_UP),
                $reason ?? '',
            );

            $service = new IcepayPaymentService();

            /**
             * @var CreateRefundResponse
             */
            $refund = $service->createRefund($refund);

            (new icepayRefund())->insertFromResponse($refund);

            if ($refund->status == 'pending' || $refund->status == 'completed') {
                exit(json_encode([
                    'ok' => true,
                    'message' => $this->module->l('Refund send', 'icepay'),
                ]));
            } else {
                exit(json_encode([
                    'ok' => false,
                    'message' => $this->module->l('Error. Please check icepay.', 'icepay'),
                ]));
            }
        } catch (Exception $e) {
            PrestaShopLogger::addLog('Icepay Refund Error: ' . $e->getMessage(), 4);

            exit(json_encode([
                'ok' => false,
                'message' => $this->module->l('Refund not send: ', 'icepay') . $e->getMessage(),
            ]));
        }
    }
}
