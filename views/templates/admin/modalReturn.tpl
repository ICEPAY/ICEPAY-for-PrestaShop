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

<div class="form-group row d-flex">
    <div class="col-6">{$transaction->key}</div>
    <div class="col-2">{$transaction->amount->value / 100}</div>
    <div class="col-4">{$transaction->status}</div>
</div>
<hr>

{if $transaction->refunds}
    <div class="form-group row d-flex">
        <div class="col-6">{l s='Refund ID' mod='icepay'}</div>
        <div class="col-2">{l s='amount' mod='icepay'}</div>
        <div class="col-4">{l s='Status' mod='icepay'}</div>

    </div>
    {foreach $transaction->refunds as $refund}
        <div class="form-group row d-flex">
            <div class="col-6">{$refund['key']}</div>
            <div class="col-2">{$refund['amount']['value'] / 100}</div>
            <div class="col-4">{$refund['status']}</div>
            <div class="col-12">{l s='Description: ' mod='icepay'}{$refund['description']}</div>
        </div>
    {/foreach}
{else}
    {l s='No previous refunds.' d='Module.Icepay.Modal'}
{/if}
<hr>