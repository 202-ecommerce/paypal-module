{*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2016 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
{if !isset($paypal_refunded)}
    <div class="col-lg-7">
        <div class="tab-content panel" id="paymentPaypal">
            <div class="panel-heading">
                <i><img src="{$path_logo}"/></i>
                {l s='Paypal' mod='paypal'}
            </div>
            <div class="tab-pane active">
                {if isset($capture_link)}
                    <a href="{$order_link|escape:'html':'UTF-8'}{$capture_link|escape:'html':'UTF-8'}" class="btn btn-primary">
                        {l s='Capture paypal' mod='paypal'}
                    </a>
                {/if}
                {if isset($refund_link)}
                    <a href="{$order_link|escape:'html':'UTF-8'}{$refund_link|escape:'html':'UTF-8'}" class="btn btn-primary">
                        {l s='Refund paypal' mod='paypal'}
                    </a>
                {/if}
            </div>
        </div>
    </div>
{/if}