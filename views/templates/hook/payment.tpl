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

<div class="row">
	<div class="col-xs-12 col-md-6">
		<p class="payment_module" id="icepay_payment_button">
			{if $cart->getOrderTotal() < 2}
				<a href="">
					<img src="{$domain|cat:$payment_button|escape:'html':'UTF-8'}" alt="{l s='Pay with my payment module' mod='icepay'}" />
					{l s='Minimum amount required in order to pay with my payment module:' mod='icepay'} {convertPrice price=2}
				</a>
			{else}
				<a href="{$link->getModuleLink('icepay', 'redirect', array(), true)|escape:'htmlall':'UTF-8'}" title="{l s='Pay with my payment module' mod='icepay'}">
					<img src="{$module_dir|escape:'htmlall':'UTF-8'}/logo.png" alt="{l s='Pay with my payment module' mod='icepay'}" width="32" height="32" />
					{l s='Pay with my payment module' mod='icepay'}
				</a>
			{/if}
		</p>
	</div>
</div>
