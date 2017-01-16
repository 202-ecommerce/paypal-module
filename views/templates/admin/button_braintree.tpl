{if $Braintree_Configured}
{l s='Your Braintree account is configured. You can join the Braintree support at xxxx' mod='paypal'}<br />

{else}
<div id="button_braintree">
</div>
<script src="https://assets.braintreegateway.com/v1/braintree-oauth-connect.js"></script>
<script>
	$(document).ready(function(){
		var user = [];
		user['country'] = '{$User_Country}';
		user['email'] = '{$User_Mail}';

		var business = [];
		business['name'] = '{$Business_Name}';
		business['country'] = '{$Business_Country}';

		$.get('{$Proxy_Host}prestashop/getUrlConnect', {
			user: user,
			business: business,
			redirect_url: '{$Braintree_Redirect_Url}'
		}).done(function(data){
			console.log(data);

			var partner = new BraintreeOAuthConnect({
				connectUrl : data.data.url_connect,
				container: 'button_braintree',
                environment: 'sandbox',
                onError: function (errorObject) {
                    console.warn(errorObject.message);
                }
			});
		});
	});
</script>
{/if}