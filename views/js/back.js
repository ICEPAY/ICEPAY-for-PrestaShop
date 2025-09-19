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


jQuery(function($) {
    var $form = $('#refundForm');
    var $refundSummary = $('#refund-summary')
    var $refundSuccess = $('#refund-success')
    var $keyInput = $('#refundTransactionKey');
    var $referenceInput = $('#refundTransactionReference');
    var $partialGroup = $('#partialAmountGroup');
    var $fullAmountRefund = $('#fullAmountRefund');
    var $amountInput = $('#refundAmount');
    var $refundError = $('#refund-error');
    var $confirmBtn = $('#refund-confirm');

    var modalDataLoaded = false;

    function togglePartial() {
        if ($('#refundPartial').is(':checked')) {
            $partialGroup.removeClass('d-none');
            $amountInput.prop('disabled', false);
        } else {
            $partialGroup.addClass('d-none');
            $amountInput.prop('disabled', true);
            $amountInput.removeClass('is-invalid');
        }
    }

    $('input[name="refund_type"]').on('change', togglePartial);

    var $ = window.$ || window.jQuery;

    // When user clicks any "refund" button, stash its transaction id in the modal
    $('body').on('click', '.js-refund-open', function () {
        var tx = $(this).data('transaction');
        modalDataLoaded = false; // reset each time

        $form[0].reset();
        $keyInput.val(tx);
        togglePartial();

        $refundSummary.html('Fetching transaction: ' + tx);
        $refundError.addClass('d-none').text('');
        $confirmBtn.prop('disabled', true)


        $.ajax({
            url: icepayAjaxUrl + '&action=Modal',
            method: 'POST',
            dataType: 'json',
            data: { transaction: tx }
        })
            .done(function (res) {
                if (res && res.summary) {
                    // success UX
                    $refundSummary.removeClass('d-none').html(res.summary);

                    //Confirm loading of the modal
                    modalDataLoaded = true;

                    $refundError.addClass('d-none').html('');
                    $referenceInput.val(res.reference);
                    $confirmBtn.prop('disabled', false);

                    if(res.max_refund){
                        $amountInput.attr('max', res.max_refund);
                        $amountInput.val(res.max_refund);
                        $fullAmountRefund.val(res.max_refund);
                    }
                    if(res.max_refund == 0){
                        $refundError.removeClass('d-none').text('Order fully refunded');
                        $confirmBtn.prop('disabled', true);
                    }

                } else {
                    $refundError.removeClass('d-none').text((res && res.error) ? res.error : 'Fetching order failed.');
                }
            })
            .fail(function (xhr) {
                $refundError.removeClass('d-none').text('Request failed: ' + (xhr.responseText || xhr.status));
            })
    });

    $($form).on('submit', function (e) {
        e.preventDefault();
        e.stopPropagation();
        return false;
    });

    // // Confirm button sends the AJAX request to your admin controller
    $(document).off('click', '#refund-confirm').on('click', '#refund-confirm', function (e) {
        e.preventDefault();

        if (!modalDataLoaded) {
            $refundError.removeClass('d-none').text('Order details not loaded yet. Please wait.');
            return false;
        }
        var reason = $('#refundReason');

        if (reason.val().length > 255) {
            $('#refund-error').removeClass('d-none').text('Reason cannot be longer than 255 characters.');
            reason.focus();
            return false;
        }

        var $btn = $(this);
        var tx = $keyInput.val();

        if (!tx) {
            $refundError.removeClass('d-none').text('Missing transaction id.');
            return;
        }

        /**
         *
         * @type {*|jQuery|string}
         *
         * Set vars for submit
         */
        var refundType = $('input[name="refund_type"]:checked').val() || 'full';
        var payload = {
            transaction: tx,
            refund_type: refundType,
            reference: $referenceInput.val() || '',
            reason: reason.val() || ''
        };
        if (refundType === 'full' && $fullAmountRefund.val()) {
            payload.amount = $fullAmountRefund.val();
        }

        payload.full_amount = $fullAmountRefund.val();

        if (refundType === 'partial') {
            var val = parseFloat($amountInput.val());
            var min = parseFloat($amountInput.attr('min')) || 0.01;
            var maxAttr = $amountInput.attr('max');
            var max = maxAttr ? parseFloat(maxAttr) : Infinity;

            if (isNaN(val) || val < min || val > max) {
                $amountInput.addClass('is-invalid').focus();
                if (!$amountInput.next('.invalid-feedback').length) {
                    $('<div class="invalid-feedback">Enter a valid amount between ' +
                        min.toFixed(2) + ' and ' + (isFinite(max) ? max.toFixed(2) : '∞') + '.</div>')
                        .insertAfter($amountInput);
                }
                return false;
            }
            $amountInput.removeClass('is-invalid');
            $amountInput.next('.invalid-feedback').remove();
            payload.amount = val.toFixed(2);
        }

        $btn.prop('disabled', true);
        $refundSuccess.addClass('d-none').empty();
        $refundError.addClass('d-none').empty();

        $.post({
            url: icepayAjaxUrl + '&action=DoRefund',
            method: 'POST',
            dataType: 'json',
            data: payload
        })
            .done(function (res) {
                if (res && res.message) {
                    // success UX
                    $('#refund-success').removeClass('d-none').html(res.message);
                    // reload to reflect new status
                    window.location.reload();
                } else {
                    $('#refund-error').removeClass('d-none').text((res && res.error) ? res.error : 'Refund failed.');
                }
            })
            .fail(function (xhr) {
                $('#refund-error').removeClass('d-none').text('Request failed: ' + (xhr.responseText || xhr.status));
            })
            .always(function () {
                $btn.prop('disabled', false);
            });
    });
});