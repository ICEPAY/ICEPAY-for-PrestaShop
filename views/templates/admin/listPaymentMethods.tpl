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
        <div class="panel-body">
            <p class="help-block">
                {l s='Configure each payment method individually. Leave all countries unchecked to allow all countries.' mod='icepay'}
            </p>

            <div class="panel-group" id="icepay-method-configurations">
                {foreach $available_methods as $method name=methods}
                    {assign var=collapseId value="icepay-method-"|cat:$smarty.foreach.methods.iteration}
                    <div class="panel panel-default js-icepay-method-panel"
                         data-method-id="{$method->id|escape:'htmlall':'UTF-8'}">
                        <div
                                class="panel-heading"
                                data-toggle="collapse"
                                data-target="#{$collapseId|escape:'htmlall':'UTF-8'}"
                                aria-expanded="false"
                                aria-controls="{$collapseId|escape:'htmlall':'UTF-8'}"
                                style="display:flex;justify-content: space-between"
                        >
                            <div class="col-lg-12 col-sm-12">
                                <strong>{$method->adminLabel|escape:'htmlall':'UTF-8'}</strong>
                                <div class="small text-muted">
                                    {$method->id|escape:'htmlall':'UTF-8'}
                                </div>
                            </div>
                            <div class="col-lg-12 col-sm-12">
                                {if isset($method->logo)}
                                    <img src="{$method->logo|escape:'htmlall':'UTF-8'}"
                                         alt="{$method->adminLabel|escape:'htmlall':'UTF-8'}"
                                         style="max-height: 32px; margin-left: 10px; width: auto;"/>
                                {/if}
                            </div>
                            <div class="col-lg-12 col-sm-12">
                                <button class="btn btn-default pull-right" type="button">
                                    {l s='Configure' mod='icepay'}
                                </button>
                            </div>
                        </div>

                        <div id="{$collapseId|escape:'htmlall':'UTF-8'}" class="panel-collapse collapse">
                            <div class="panel-body">
                                <div class="form-group">
                                    <label class="control-label">
                                        {l s='Countries' mod='icepay'}
                                    </label>
                                    <div class="well" style="max-height: 260px; overflow-y: auto; margin-bottom: 10px;">
                                        {foreach $country_options as $country}
                                            <div class="checkbox">
                                                <label for="countries_{$smarty.foreach.methods.iteration|escape:'htmlall':'UTF-8'}_{$country.iso_code|escape:'htmlall':'UTF-8'}">
                                                    <input
                                                            id="countries_{$smarty.foreach.methods.iteration|escape:'htmlall':'UTF-8'}_{$country.iso_code|escape:'htmlall':'UTF-8'}"
                                                            class="js-icepay-country"
                                                            type="checkbox"
                                                            name="ICEPAY_METHOD_COUNTRIES[{$method->id|escape:'htmlall':'UTF-8'}][]"
                                                            value="{$country.iso_code|escape:'htmlall':'UTF-8'}"
                                                            {if isset($method_country_lookup[$method->id][$country.iso_code])}checked="checked"{/if}
                                                    />
                                                    {$country.name|escape:'htmlall':'UTF-8'}
                                                    ({$country.iso_code|escape:'htmlall':'UTF-8'})
                                                </label>
                                            </div>
                                        {/foreach}
                                    </div>
                                    <p class="help-block">
                                        {l s='Check one or more countries for this method. Leave everything unchecked to allow all countries.' mod='icepay'}
                                    </p>
                                </div>
                                <button
                                        type="button"
                                        class="btn btn-primary js-icepay-save-method"
                                        data-method-id="{$method->id|escape:'htmlall':'UTF-8'}"
                                        data-collapse-target="#{$collapseId|escape:'htmlall':'UTF-8'}"
                                >
                                    <i class="process-icon-save"></i> {l s='Save' mod='icepay'}
                                </button>
                            </div>
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
    </div>
{else}
    <div class="alert alert-warning">{l s='No available payment methods found.' mod='icepay'}</div>
{/if}
