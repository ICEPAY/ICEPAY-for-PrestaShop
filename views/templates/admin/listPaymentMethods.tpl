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
    <form method="post" action="{$method_settings_form_action|escape:'htmlall':'UTF-8'}">
        <input type="hidden" name="token" value="{$method_settings_token|escape:'htmlall':'UTF-8'}" />

        <div class="panel">
            <h3><i class="icon-credit-card"></i> Available Payment Methods</h3>
            <p class="help-block">
                {l s='Configure each payment method individually. Leave the country list empty to allow all countries. ' mod='icepay'}
            </p>

            <div class="panel-group" id="icepay-method-configurations">
                {foreach $available_methods as $method name=methods}
                    {assign var=collapseId value="icepay-method-"|cat:$smarty.foreach.methods.iteration}
                    <div class="panel panel-default">
                        <div class="d-flex"
                             style="display:flex;justify-content: space-between"
                             data-toggle="collapse"
                             data-target="#{$collapseId|escape:'htmlall':'UTF-8'}"
                             aria-expanded="false"
                             aria-controls="{$collapseId|escape:'htmlall':'UTF-8'}">
                            <div class="col">
                                <strong>{$method->adminLabel|escape:'htmlall':'UTF-8'}</strong>
                                <div class="small text-muted">
                                    {$method->id|escape:'htmlall':'UTF-8'}
                                </div>
                                {if isset($method->logo)}
                                    <img src="{$method->logo|escape:'htmlall':'UTF-8'}" alt="{$method->adminLabel|escape:'htmlall':'UTF-8'}" style="max-height: 32px; margin-left: 10px;" />
                                {/if}
                            </div>
                            <div class="col">
                                <button
                                    class="btn btn-default"
                                    type="button"
                                >
                                    {l s='Configure' mod='icepay'}
                                </button>
                            </div>
                        </div>

                        <div id="{$collapseId|escape:'htmlall':'UTF-8'}" class="panel-collapse collapse">
                            <div class="panel-body">
                                <input type="hidden" name="ICEPAY_METHOD_IDS[]" value="{$method->id|escape:'htmlall':'UTF-8'}" />

                                <div class="form-group">
                                    <label class="control-label" for="countries_{$smarty.foreach.methods.iteration|escape:'htmlall':'UTF-8'}">
                                        {l s='Countries' mod='icepay'}
                                    </label>
                                    <select
                                        id="countries_{$smarty.foreach.methods.iteration|escape:'htmlall':'UTF-8'}"
                                        name="ICEPAY_METHOD_COUNTRIES[{$method->id|escape:'htmlall':'UTF-8'}][]"
                                        class="form-control"
                                        multiple="multiple"
                                        size="8"
                                    >
                                        {foreach $country_options as $country}
                                            <option
                                                value="{$country.iso_code|escape:'htmlall':'UTF-8'}"
                                                {if isset($method_country_lookup[$method->id][$country.iso_code])}selected="selected"{/if}
                                            >
                                                {$country.name|escape:'htmlall':'UTF-8'} ({$country.iso_code|escape:'htmlall':'UTF-8'})
                                            </option>
                                        {/foreach}
                                    </select>
                                    <p class="help-block">
                                        {l s='No selection means all countries.' mod='icepay'}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>

            <div class="panel-footer">
                <button type="submit" name="submitIcepayModule" class="btn btn-default pull-right">
                    <i class="process-icon-save"></i> {l s='Save payment method configuration' mod='icepay'}
                </button>
            </div>
        </div>
    </form>
{else}
    <div class="alert alert-warning">{l s='No available payment methods found.' mod='icepay'}</div>
{/if}
