{if $Braintree_Configured}
Braintree OK<br />
Access Token: {$Braintree_Access_Token}<br />
Refresh Token: {$Braintree_Refresh_Token}<br />
Expires At: {$Braintree_Expires_At}<br />
<button id="test_refresh">Test refresh</button>
<script>
	$(document).ready(function(){
		$('#test_refresh').on('click', function(){
			$.get('{$Proxy_Host}prestashop/refreshToken', {
				refreshToken: '{$Braintree_Refresh_Token}'
			}).done(function(data){
				console.log(data);

				$.post('/modules/paypal/endpoint.php', {
					accessToken: data.data.accessToken,
					expiresAt: data.data.expiresAt.date,
					refreshToken: data.data.refreshToken
				}).done(function(){
					location.reload();
				});
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
		$.get('{$Proxy_Host}prestashop/getUrlConnect', {
			user_country: '{$User_Country}',
			user_email:'{$User_Mail}',
			business_name: '{$Business_Name}',
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