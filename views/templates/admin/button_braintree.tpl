{if $Braintree_Configured}
<span style="color:#008000;">
{if $PayPal_sandbox_mode}
	{l s='Your Braintree account is configured in sandbox mode. You can join the Braintree support on 08 05 54 27 14' mod='paypal'}
{else}
	{l s='Your Braintree account is configured in live mode. You can join the Braintree support on 08 05 54 27 14' mod='paypal'}
{/if}
</span>
{else}
<div id="button_braintree">
</div>
<script src="https://assets.braintreegateway.com/v1/braintree-oauth-connect.js"></script>
<script>
	$(document).ready(function(){
		$.get('{$Proxy_Host}prestashop/getUrlConnect', {
			user_country: '{$User_Country}',
			user_email:'{$User_Mail}',
			business_name: '{$Business_Name}',
			redirect_url: '{$Braintree_Redirect_Url}'
		}).done(function(data){
			//console.log(data);

			var partner = new BraintreeOAuthConnect({
				connectUrl : data.data.url_connect,
				container: 'button_braintree',
                environment: {if $PayPal_sandbox_mode}'sandbox'{else}'production'{/if},
                onError: function (errorObject) {
                    console.warn(errorObject.message);
                }
			});
		});
	});
</script>
{/if}