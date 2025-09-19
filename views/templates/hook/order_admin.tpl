{*
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
* DISCLAIMER
*
* This file is part of the Channel-support module.
* If you wish to customize this module, feel free to do so under the terms of the license.
*
*  @author    Channel Support <info@channel-support.nl>
*  @copyright 2025 Channel-support BV
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<div class="card mt-2" id="icepay">
    <div class="card-header">
        <h3 class="card-header-title">
            {l s='ICEPAY' mod='icepay'}
        </h3>
    </div>

    <div class="card-body">

        {foreach $payment_rows as $row}
            <div class="form-group row d-flex">
                <div class="col-4">{l s='Transactie ID' mod='icepay'}: {$row['key']}</div>
                <div class="col-4">{l s='Status' mod='icepay'}: {$row['status']}</div>
                <div class="col-4 actions">
                    <button
                            class="btn btn-danger js-refund-open"
                            data-toggle="modal"
                            data-target="#refundModal"
                            data-transaction="{$row['key']|escape:'html':'UTF-8'}"
                    >
                        {l s='refund' mod='icepay'}
                    </button>
                </div>
            </div>
            {if $row['refunds']}
                {foreach $row['refunds'] as $refund}
                    <div class="form-group row d-flex">
                        <div class="col-4">{l s='Refund ID' mod='icepay'}: {$refund['key_refund']}</div>
                        <div class="col-4">{l s='Status' mod='icepay'}: {$refund['status']}</div>
                    </div>
                {/foreach}
            {/if}
        {/foreach}
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="refundModal" tabindex="-1" role="dialog" aria-labelledby="refundModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="refundForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="refundModalLabel">Issue refund</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                <div class="modal-body">
                    <!-- Transaction key -->
                    <input type="hidden" name="transaction_key" id="refundTransactionKey">
                    <input type="hidden" name="transaction_reference" id="refundTransactionReference">
                    <input type="hidden" name="full_amount" id="fullAmountRefund">

                    <div id="refund-error" class="alert alert-danger d-none"></div>
                    <div id="refund-success" class="alert alert-success d-none"></div>
                    <p id="refund-summary">{* optionally fill via JS if you fetch extra info *}</p>
                    <!-- Refund type -->
                    <div class="form-group">
                        <label class="d-block mb-2">Refund type</label>

                        <div class="custom-control custom-radio">
                            <input class="custom-control-input" type="radio" name="refund_type" id="refundFull" value="full" checked>
                            <label class="custom-control-label" for="refundFull">Full amount</label>
                        </div>

                        <div class="custom-control custom-radio">
                            <input class="custom-control-input" type="radio" name="refund_type" id="refundPartial" value="partial">
                            <label class="custom-control-label" for="refundPartial">Partial</label>
                        </div>
                    </div>

                    <!-- Partial amount -->
                    <div class="form-group d-none" id="partialAmountGroup">
                        <label for="refundAmount">Amount</label>
                        <input
                                type="number"
                                step="0.01"
                                min="0.01"
                                class="form-control"
                                id="refundAmount"
                                name="amount"
                                placeholder="0.00"
                                disabled
                        >
                        <small class="form-text text-muted" id="refundAmountHelp"></small>
                    </div>

                    <!-- Optional reason -->
                    <div class="form-group">
                        <label for="refundReason">Reason (optional)</label>
                        <textarea class="form-control" id="refundReason" name="reason" rows="2" maxlength="500"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-danger" type="button" id="refund-confirm">Confirm refund</button>
                </div>
            </div>
        </form>
    </div>
</div>