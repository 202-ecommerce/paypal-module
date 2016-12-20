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
    <div class="panel">
        <img src="{$path}/views/img/paypal.png">
        {l s='Paypal products' mod='paypal'} :
        <a data-paypal-button="true" href="{$PartnerboardingURL}" target="PPFrame"> | {l s='Modifier' mod='paypal'}</a>
    </div>
    <div class="panel">
        <div class="panel-heading">{l s='Configuaration required' mod='paypal'}</div>
        <div class="panel-body">
            <label class="control-label col-lg-3">{l s='Activate solution Paypal' mod='paypal'}</label>
            <div class="col-lg-9">
                <a data-paypal-button="true" href="{$PartnerboardingURL}" target="PPFrame">{l s='Sign up for PayPal' mod='paypal'}</a>
            </div>
        </div>
    </div>
</div>