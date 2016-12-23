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
    <li class="active"><a data-toggle="pill" href="#paypal_conf"><span>{l s='Configuration' mod='paypal'}</span></a></li>
    <li><a data-toggle="pill" href="#paypal_params"><span>{l s='Parametres' mod='paypal'}</span></a></li>
</ul>
<div class="tab-content">
    <div id="paypal_conf"  class="tab-pane fade in active">
        <div class="col-sm-12">
            <div class="panel">
                <img src="{$path|escape:'html':'UTF-8'}/views/img/paypal.png">
                {l s='Paypal products' mod='paypal'} :
                <a data-paypal-button="true" href="{$PartnerboardingURL|escape:'html':'UTF-8'}" target="PPFrame"> | {l s='Modifier' mod='paypal'}</a>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="panel paypal-signup">
                <div class="panel-heading">
                    <img src="{$path|escape:'html':'UTF-8'}/views/img/paypal_icon.png">
                    {l s='Configuaration required' mod='paypal'}</div>
                <div class="panel-body">
                    <label class="control-label col-lg-3">{l s='Activate solution Paypal' mod='paypal'}</label>
                    <div class="col-lg-9">
                        <a data-paypal-button="true" class="btn btn-info" href="{$PartnerboardingURL|escape:'html':'UTF-8'}" target="PPFrame">
                            <img src="{$path|escape:'html':'UTF-8'}/views/img/paypal_court.png" width="30px;">
                            {l s='Sign up for PayPal' mod='paypal'}
                        </a>
                    </div>
                </div>
            </div>
        </div>
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
                        {l s='Acceptez des paiements via notre produit PayPal Express Checkout. Vous toucherez nos plus de 192 millions de comptes PayPal actifs à travers le monde.' mod='paypal'}
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
    <div id="paypal_params" class="tab-pane fade">
        <div class="parametres">
            <div class="panel">
                <img src="{$path|escape:'html':'UTF-8'}/views/img/paypal.png">
                {l s='Paypal products' mod='paypal'} :
                <a data-paypal-button="true" href="{$PartnerboardingURL|escape:'html':'UTF-8'}" target="PPFrame"> | {l s='Modifier' mod='paypal'}</a>
            </div>
        </div>

    </div>
</div>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        $('#configuration_form').insertAfter($('.parametres'));
    });

</script>