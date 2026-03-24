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

jQuery(function ($) {
    function showGrowl(type, message) {
        if (type === 'success' && typeof window.showSuccessMessage === 'function') {
            window.showSuccessMessage(message);
            return;
        }

        if (type === 'error' && typeof window.showErrorMessage === 'function') {
            window.showErrorMessage(message);
            return;
        }

        if (typeof window.$ !== 'undefined' && window.$.growl) {
            window.$.growl[type]({ title: '', message: message });
            return;
        }

        window.alert(message);
    }

    $(document).on('click', '.js-icepay-save-method', function (event) {
        event.preventDefault();

        var $button = $(this);
        var methodId = $button.data('method-id');
        var collapseTarget = $button.data('collapse-target');
        var $panel = $button.closest('.js-icepay-method-panel');
        var countries = [];

        $panel.find('.js-icepay-country:checked').each(function () {
            countries.push($(this).val());
        });

        $button.prop('disabled', true).text(icepayConfigMessages.saveInProgress);

        $.ajax({
            url: icepayAjaxUrl + '&action=SavePaymentMethodConfig',
            method: 'POST',
            dataType: 'json',
            data: {
                method_id: methodId,
                countries: countries
            }
        }).done(function (response) {
            if (!response || response.ok !== true) {
                showGrowl('error', response && response.message ? response.message : icepayConfigMessages.saveError);
                return;
            }

            $(collapseTarget).collapse('hide');
            showGrowl('success', response.message || icepayConfigMessages.saveSuccess);
        }).fail(function () {
            showGrowl('error', icepayConfigMessages.saveError);
        }).always(function () {
            $button.prop('disabled', false).html('<i class="process-icon-save"></i> Save');
        });
    });
});
