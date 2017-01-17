{if $Braintree_Configured}
{l s='Your Braintree account is configured. You can join the Braintree support at xxxx' mod='paypal'}<br />

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