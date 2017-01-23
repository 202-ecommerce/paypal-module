{if $Braintree_Configured}
<span style="color:#008000;">
{if $PayPal_sandbox_mode}
	{l s='Your Braintree account is configured in sandbox mode. You can join the Braintree support at xxxx' mod='paypal'}
{else}
	{l s='Your Braintree account is configured in live mode. You can join the Braintree support at xxxx' mod='paypal'}
{/if}
</span>
{*
<div id="paypal_3D_secure">
	<p>{l s='Enabled 3D secure ?' mod='paypal'}</p>
	<input type="radio" name="check3Dsecure" id="paypal_3Dsecure_enabled" value="1" {if $PayPal_check3Dsecure == 1}checked="checked"{/if} /> <label for="paypal_3Dsecure_enabled">{l s='yes' mod='paypal'}</label><br />
	<input type="radio" name="check3Dsecure" id="paypal_3Dsecure_disabled" value="0" {if $PayPal_check3Dsecure == 0}checked="checked"{/if} /> <label for="paypal_3Dsecure_disabled">{l s='no' mod='paypal'}</label>
</div>
*}
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