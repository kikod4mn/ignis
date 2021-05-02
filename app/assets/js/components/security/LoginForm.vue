<template>
	<form :action="submitPath" method="post" class="material__form">
		<input type="hidden" name="security_login[_token]" :value="csrfToken" />
		
		<base-text-field
			id="login-email" type="email" name="security_login[_email]" :value="lastUsername" required="required" label="Email"
		></base-text-field>
		
		<base-password-field
			id="login-password" name="security_login[_password]" label="Password" required="required"
		></base-password-field>
		
		<base-check-box-field
			id="RememberMe" name="security_login[_remember_me]" label="Remember Me"
		></base-check-box-field>
		
		<div class="row">
			<button id="Login" class="" type="submit">Sign in</button>
		</div>
	</form>
</template>

<script>
import BaseTextField     from '../form/base-fields/BaseTextField';
import BaseCheckBoxField from '../form/base-fields/BaseCheckBoxField';
import BasePasswordField from '../form/base-fields/BasePasswordField';

export default {
	components: { BasePasswordField, BaseCheckBoxField, BaseTextField },
	props     : { submitPath: String, csrfToken: String, lastUsername: String },
}
</script>