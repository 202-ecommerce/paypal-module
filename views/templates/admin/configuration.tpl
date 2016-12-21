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
<div class="container-fluid">
<ul class="nav nav-pills">
    <li class="active"><a data-toggle="pill" href="#paypal_conf"><span>{l s='Configuration' mod='paypal'}</span></a></li>
    <li><a data-toggle="pill" href="#paypal_params"><span>{l s='Parametres' mod='paypal'}</span></a></li>
</ul>
<div class="tab-content">
    <div id="paypal_conf"  class="tab-pane fade in active">
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
    <div id="paypal_params" class="tab-pane fade">

    </div>
</div>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        $('#paypal_params').append($('#configuration_form'));
    });

</script>