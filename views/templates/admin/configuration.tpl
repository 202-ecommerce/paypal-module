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
                <p class="paypal-bold">{l s='Pays du marchand' mod='paypal'} {$country|escape:'html':'UTF-8'}</p>
                <p><i>
                    {l s='Si non spécifié, le pays par défaut est sélectionné. Pour modifier' mod='paypal'}
                    <a target="_blank" href="{$localization|escape:'html':'UTF-8'}">{l s='International > Localization' mod='paypal'}</a>
                </i></p>
                <p class="paypal-bold">
                    {l s='Développez votre activité avec PayPal et bénéficiez d’une solution clé en main pour votre activité en ligne, sur mobile et à l’international.' mod='paypal'}
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
            <p><b>{l s='Products that you have chosen'}</b></p>
            <div class="col-sm-6">
                <div class="panel">
                    <img class="paypal-products" src="{$path|escape:'html':'UTF-8'}/views/img/paypal.png">
                    <span>{l s='Paypal' mod='paypal'}</span>
                    <p>
                        {l s='Acceptez des paiements via notre produit PayPal Express Checkout. Vous toucherez nos plus de 192 millions de comptes PayPal actifs à travers le monde.' mod='paypal'}
                    </p>
                    <p><a herf="#">{l s='See more' mod='paypal'}</a></p>
                    <div class="bottom">
                    <img src="{$path|escape:'html':'UTF-8'}/views/img/paypal_btm.png" class="product-img">
                    <a data-paypal-button="true" class="btn btn-default pull-right" href="{$PartnerboardingURL|escape:'html':'UTF-8'}" target="PPFrame">{l s='Activate' mod='paypal'}</a>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="panel">
                    <img class="paypal-products" src="{$path|escape:'html':'UTF-8'}/views/img/paypal.png">
                    <span>{l s='Paypal and card' mod='paypal'}</span>
                    <p>
                        {l s='Acceptez des paiements via notre produit PayPal Express Checkout. Non seulement vous acceptez PayPal avec ces 192 millions de comptes actifs mais aussi vous acceptez de nombreuses cartes bancaires à travers le monde.' mod='paypal'}
                    </p>
                    <p><a herf="#">{l s='See more' mod='paypal'}</a></p>
                    <div class="bottom">
                    <img src="{$path|escape:'html':'UTF-8'}/views/img/paypal_btm.png" class="product-img">
                    <a data-paypal-button="true" class="btn btn-default pull-right" href="{$PartnerboardingURL|escape:'html':'UTF-8'}" target="PPFrame">{l s='Activate' mod='paypal'}</a>
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
                    {l s='Paypal products' mod='paypal'} :
                    <a data-paypal-button="true" href="{$PartnerboardingURL|escape:'html':'UTF-8'}" target="PPFrame"> | {l s='Modifier' mod='paypal'}</a>
                    <p>{l s='Acceptez les paiements via PayPal et optimisez votre conversion.Accélérez les paiements de vos clients PayPal et avec One TouchTM, ils pourront régler leurs achats en un clin d\'œil.' mod='paypal'}</p>
                    <a href="#"><b>{l s='En savoir plus sur le site PayPal' mod='paypal'}</b></a>
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

<script type="text/javascript">
    $(document).ready(function(){
        $('#configuration_form').insertAfter($('.parametres'));
        var activate_link = "{$PartnerboardingURL|escape:'html':'UTF-8'}";

        $('#configuration_form input[name=paypal_sandbox]').change(function(event) {
            sandbox = $('#configuration_form input[name=paypal_sandbox]:checked').val();
            $.ajax(
            {
                type : 'POST',
                url : "{$path}"+"ajax/ajax.php?"+'sandbox='+sandbox,
                dataType: 'json',
            });
            if(sandbox == 0) {
                $.fancybox({
                    helpers : {
                        title: {
                            type: 'outside',
                            position: 'top'
                        }
                    },
                    afterClose: function() {
                        $.ajax(
                        {
                            type : 'POST',
                            url : "{$path}"+"ajax/ajax.php?resetSandbox=1",
                            dataType: 'json',
                        });
                        $('#configuration_form #paypal_sandbox_on').attr('checked', true);
                    },
                    'width':400,
                    'height':200,
                    'autoSize' : false,
                    wrapCSS    : 'fancybox-paypal',
                    'content': "<div id='ConfirmLive'><p><b>{l s='Désactiver le mode sandbox' mod='paypal'}</b></p>"+
                    "<p>{l s='Vous souhaitez désactiver le mode sandbox.' mod='paypal'}</p>"+
                    "<p style='margin-bottom: 30px;'>{l s='Nous vous redirigeons vers PayPal pour configurer le produit PayPal en production.' mod='paypal'}</p>"+
                    "<a class='btn fancybox_close' role='button'>{l s='Rester en sandbox' mod='paypal'}</a><a href='"+activate_link+"' class='btn btn-info' role='button'>{l s='Passer en production' mod='paypal'}</a></div>"
                });
                $('.fancybox_close').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $(this).parent().hide();
                    $.fancybox.close();
                });
            }
        });
    });

</script>