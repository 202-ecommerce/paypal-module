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

<div dir="ltr" style="text-align: left;" trbidi="on">
    <script type="text/javascript">
         (function(d, s, id){
         var js, ref = d.getElementsByTagName(s)[0];
            if (!d.getElementById(id)){
                js = d.createElement(s); js.id = id; js.async = true;
                js.src = "https://www.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js";
                ref.parentNode.insertBefore(js, ref);
            }
         }(document, "script", "paypal-js"));
    </script>
</div>
<div class="container-fluid paypal-nav">
<ul class="nav nav-pills">
    <li class="active"><a data-toggle="pill" href="#paypal_conf"><span>{l s='Products' mod='paypal'}</span></a></li>
    <li><a data-toggle="pill" href="#paypal_params"><span>{l s='Parametres' mod='paypal'}</span></a></li>
</ul>
    <div class="tab-content">
    <div id="paypal_conf"  class="tab-pane fade in active">
        <div class="box half left">
            <div class="logo">
                 <img src="{$path|escape:'html':'UTF-8'}/views/img/paypal_btm.png" alt=""  />
            </div>
            <div class="info">
                <p class="paypal-bold">{l s='Country of the dealer' mod='paypal'} {$country|escape:'html':'UTF-8'}</p>
                <p><i>
                    {l s='If not specified, the default country is selected. To modify : ' mod='paypal'}
                    <a target="_blank" href="{$localization|escape:'html':'UTF-8'}">{l s='International > Localization' mod='paypal'}</a>
                </i></p>
                <p class="paypal-bold">
                    {l s='Grow your business with PayPal and get a turnkey solution for your online business, mobile and internationally.' mod='paypal'}
                </p>
            </div>
        </div>

        <div class="box half right">
            <ul class="tick">
                <li><span class="paypal-bold">{l s='Get more buyers' mod='paypal'}</span><br />{l s='100 million-plus PayPal accounts worldwide' mod='paypal'}</li>
                <li><span class="paypal-bold">{l s='Access international buyers' mod='paypal'}</span><br />{l s='190 countries, 25 currencies' mod='paypal'}</li>
                <li><span class="paypal-bold">{l s='Reassure your buyers' mod='paypal'}</span><br />{l s='Buyers don\'t need to share their private data' mod='paypal'}</li>
                <li><span class="paypal-bold">{l s='Accept all major payment method' mod='paypal'}</span></li>
            </ul>
        </div>
        <div style="clear:both;"></div>

        <div class="active-products">
            <p><b>{l s='2 PayPal products selected for you'}</b></p>
            <div class="col-sm-6">
                <div class="panel">
                    <img class="paypal-products" src="{$path|escape:'html':'UTF-8'}/views/img/paypal.png">
                    <span>{l s='Paypal' mod='paypal'}</span>
                    <p>
                        {l s='Accept payments via our PayPal Express Checkout product. You will receive our more than 192 million active PayPal accounts worldwide.' mod='paypal'}
                    </p>
                    <p><a href="#">{l s='See more' mod='paypal'}</a></p>
                    <div class="bottom">
                    <img src="{$path|escape:'html':'UTF-8'}/views/img/paypal_btm.png" class="product-img">
                    <a class="btn btn-default pull-right" href="#{*$return_url|escape:'html':'UTF-8'}&method=EXPRESS_CHECKOUT*}"  onclick="display_popup('EXPRESS_CHECKOUT',0)">{l s='Activate' mod='paypal'}</a>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="panel">
                    <img class="paypal-products" src="{$path|escape:'html':'UTF-8'}/views/img/paypal.png">
                    <span>{l s='Paypal and card' mod='paypal'}</span>
                    <p>
                        {l s='Accept payments via our PayPal Express Checkout product. Not only do you accept PayPal with these 192 million active accounts but also you accept many bank cards throughout the world.' mod='paypal'}
                    </p>
                    <p><a href="#">{l s='See more' mod='paypal'}</a></p>
                    <div class="bottom">
                        <img src="{$path|escape:'html':'UTF-8'}/views/img/paypal_btm.png" class="product-img">
                        <img src="{$path|escape:'html':'UTF-8'}/views/img/mastercard.png" class="product-img">
                        <img src="{$path|escape:'html':'UTF-8'}/views/img/visa.png" class="product-img">
                        <img src="{$path|escape:'html':'UTF-8'}/views/img/discover.png" class="product-img">
                        <img src="{$path|escape:'html':'UTF-8'}/views/img/american_express.png" class="product-img">
                        <img src="{$path|escape:'html':'UTF-8'}/views/img/maestro.png" class="product-img">
                        <a class="btn btn-default pull-right" href="#{*$return_url|escape:'html':'UTF-8'}&method=EXPRESS_CHECKOUT*}" onclick="display_popup('EXPRESS_CHECKOUT',1)">{l s='Activate' mod='paypal'}</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div id="paypal_params" class="tab-pane fade col-sm-12">
        <div class="panel parametres">
            <div class="panel-body">
                <div class="col-sm-8 help-left">
                    <img src="{$path|escape:'html':'UTF-8'}/views/img/paypal.png">
                    {l s='Paypal products' mod='paypal'} : <b>{$active_products|escape:'html':'UTF-8'}</b>
                    <a id="change_product" href=""> | {l s='Edit' mod='paypal'}</a>
                    <p>{l s='Accept payments via PayPal and optimize conversion. Accelerate payments from your PayPal customers with One Touch ™, they can pay for their purchases in the blink\'s eye.' mod='paypal'}</p>
                    <a href="#"><b>{l s='Learn more about PayPal' mod='paypal'}</b></a>
                    </p>
                </div>
                <div class="col-sm-3 help-right">
                    {l s='Need help' mod='paypal'} ?
                    <a href="#">{l s='Contact us' mod='paypal'}</a>
                </div>
            </div>
        </div>
    </div>

</div>
</div>
<div style="display: none;">
    <div id="content-fancybox-configuration">
        <form action="{$return_url}" method="post" id="credential-configuration" class="bootstrap">
            <h4>{l s='API Credentials' mod='paypal'}</h4>
            <hr/>
            <p>{l s='In order to accept PayPal payments, please fill your API REST credentials.' mod='paypal'}</p>
            <ul>
                <li>{l s='Access'  mod='paypal'} <a target="_blank" href="https://developer.paypal.com/developer/applications/">{l s='https://developer.paypal.com/developer/applications/' mod='paypal'}</a></li>
                <li>{l s='Create a « REST API apps »' mod='paypal'}</li>
                <li>{l s='Click « Show » en dessous de « Secret: »' mod='paypal'}</li>
                <li>{l s='Copy/paste your « Client ID » and « Secret » below for each environment' mod='paypal'}</li>
            </ul>
            <hr/>
            <input type="hidden" id="method" name="method"/>
            <input type="hidden" id="with_card" name="with_card"/>
            <h5>{l s='Sandbox' mod='paypal'}</h5>
            <p>
                <label for="sandbox_client_id">{l s='Client ID' mod='paypal'}</label>
                <input type="text" id="sandbox_client_id" name="sandbox[client_id]" value="{$PAYPAL_SANDBOX_CLIENTID}"/>
            </p>
            <p>
                <label for="sandbox_secret">{l s='Secret' mod='paypal'}</label>
                <input type="password" id="sandbox_secret" name="sandbox[secret]" value="{$PAYPAL_SANDBOX_SECRET}"/>
            </p>
            <hr/>
            <h5>{l s='Live' mod='paypal'}</h5>
            <p>
                <label for="live_client_id">{l s='Client ID' mod='paypal'}</label>
                <input type="text" id="live_client_id" name="live[client_id]" value="{$PAYPAL_LIVE_CLIENTID}"/>
            </p>
            <p>
                <label for="live_secret">{l s='Secret' mod='paypal'}</label>
                <input type="password" id="live_secret" name="live[secret]" value="{$PAYPAL_LIVE_SECRET}"/>
            </p>
            <p>
                <button class="btn btn-default"  onclick="$.fancybox.close();return false;">{l s='Cancel' mod='paypal'}</button>
                <button class="btn btn-info" name="save_credentials">{l s='Confirm API Credentials' mod='paypal'}</button>
            </p>
        </form>
    </div>

</div>

<script type="text/javascript">
    function display_popup(method,with_card)
    {
        $('#method').val(method);
        $('#with_card').val(with_card);
        $.fancybox.open([
            {
                type: 'inline',
                autoScale: true,
                minHeight: 30,
                content: $('#content-fancybox-configuration').html(),
            }
        ]);
    }

    $(document).ready(function(){



        $('#change_product').click(function(event) {
            event.preventDefault();
            $('a[href=#paypal_conf]').click();
        });
        
        $('#configuration_form').insertAfter($('.parametres'));
        //var activate_link = "{*$PartnerboardingURL|escape:'html':'UTF-8'*}";

    });

</script>