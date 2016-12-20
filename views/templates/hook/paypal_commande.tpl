
<div class="tab-content panel" id="paymentPaypal">
    <div class="panel-heading">
        <i class="icon-money"></i>
        {l s='Paypal' mod='paypal'}
    </div>
    <div class="tab-pane active">
        {if isset($capture_link)}
            <a href="{$link_suivi|escape:'html':'UTF-8'}{$capture_link|escape:'html':'UTF-8'}" class="btn btn-primary">
                {l s='Capture paypal' mod='paypal'}
            </a>
        {/if}
        <a href="{$link_suivi|escape:'html':'UTF-8'}&{$refund_link|escape:'html':'UTF-8'}" class="btn btn-primary">
            {l s='Refund paypal' mod='paypal'}
        </a>
    </div>
</div>