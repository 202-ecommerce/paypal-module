{if $Braintree_Configured}
{l s='Your Braintree account is configured. You can ask a new token with the button below' mod='paypal'}<br />
<button id="refresh">{l s='Ask a new token' mod='paypal'}</button>
<script>
	$(document).ready(function(){
		$('#refresh').on('click', function(){
			$.get('{$Proxy_Host}prestashop/refreshToken', {
				refreshToken: '{$Braintree_Refresh_Token}'
			}).done(function(data){
				document.location.href = '{$Braintree_Redirect_Url}&accessToken='+encodeURIComponent(data.data.accessToken)+'&expiresAt='+encodeURIComponent(data.data.expiresAt)+'&refreshToken='+encodeURIComponent(data.data.refreshToken);
			});
		});
	});
</script>
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