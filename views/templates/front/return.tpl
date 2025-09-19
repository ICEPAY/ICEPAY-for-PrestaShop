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


{extends file='page.tpl'}

{block name='page_content'}


    {if Tools::isSubmit('failed')}
        <h1>{l s='Please contact us!' mod='icepay'}</h1>
        <p>
            <a href="{$link->getPageLink('contact', true)}">{l s='Contact us on this page.' mod='icepay'}</a>
            {if icepayTransaction   }
                {l s='using your transaction id:' mod='icepay'} {$icepayTransaction|escape:'htmlall':'UTF-8'}
            {/if}
            <br/>
            <a class="btn btn-primary mt-3" href="{$try_again_link|escape:'htmlall':'UTF-8'}">{l s='Try again' mod='icepay'}</a>
        </p>
    {else}
        <!-- Your custom HTML content -->
        <h1>{l s='Fetching the paymentstatus!' mod='icepay'}</h1>
        <p>{l s='We are processing the transaction... Please give us a moment to fetch the status' mod='icepay'}</p>

    {/if}
{/block}