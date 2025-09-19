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

<div>
	<h3>{l s='Redirect your customer' mod='icepay'}:</h3>
	<ul class="alert alert-info">
			<li>{l s='This action should be used to redirect your customer to the website of your payment processor' mod='icepay'}.</li>
	</ul>
	
	<div class="alert alert-warning">
		{l s='You can redirect your customer with an error message' mod='icepay'}:
		<a href="{$link->getModuleLink('icepay', 'redirect', ['action' => 'error'], true)|escape:'htmlall':'UTF-8'}" title="{l s='Look at the error' mod='icepay'}">
			<strong>{l s='Look at the error message' mod='icepay'}</strong>
		</a>
	</div>
	
	<div class="alert alert-success">
		{l s='You can also redirect your customer to the confirmation page' mod='icepay'}:
		<a href="{$link->getModuleLink('icepay', 'confirmation', ['cart_id' => $cart_id, 'secure_key' => $secure_key], true)|escape:'htmlall':'UTF-8'}" title="{l s='Confirm' mod='icepay'}">
			<strong>{l s='Go to the confirmation page' mod='icepay'}</strong>
		</a>
	</div>
</div>
