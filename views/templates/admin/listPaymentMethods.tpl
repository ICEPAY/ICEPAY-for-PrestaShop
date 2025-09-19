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

{if $available_methods}
    <div class="panel">
        <h3><i class="icon-credit-card"></i> Available Payment Methods</h3>
        <table class="table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Logo</th>
                <th>Description</th>
            </tr>
            </thead>
            <tbody>

            {foreach $available_methods as $method}
                <tr>
                    <td>{$method->id|escape:'htmlall':'UTF-8'}</td>
                    <td>
                        {if isset($method->logo)}
                            <img src="{$method->logo|escape:'htmlall':'UTF-8'}" alt="{$method->description|escape:'htmlall':'UTF-8'}" style="max-height: 32px;" />
                        {/if}
                    </td>
                    <td>{$method->description|escape:'htmlall':'UTF-8'}</td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
{else}
    <div class="alert alert-warning">No available payment methods found.</div>
{/if}