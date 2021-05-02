<template>
	<form :action="submitPath" method="POST" class="material__form">
		<input id="register__token" type="hidden" name="password_change_with_token[_token]" :value="csrfToken" />
		
		<base-password-field
			id="password_change_with_token_first"
			name="password_change_with_token[_password]"
			label="Password"
			required="required"
			@input.native="validatePassword($event.target.value)"
		></base-password-field>
		
		<div class="form-group">
			<div class="col-12">
				<button id="Submit" class="btn btn-primary w-100" type="submit">Save changes</button>
			</div>
		</div>
		
		<div class="form-group">
			<div class="validation__strength">
				<span :class="bar1()"></span>
				<span :class="bar2()"></span>
				<span :class="bar3()"></span>
				<span :class="bar4()"></span>
				
				<ul class="list-unstyled">
					<li><i :class="validation1()"></i> must be at least 12 characters</li>
					<li><i :class="validation2()"></i> must contain at least one capital letter</li>
					<li><i :class="validation3()"></i> must contain minimum of one number</li>
					<li><i :class="validation4()"></i> must contain at least one symbol $&+,:;=?@#</li>
				</ul>
			</div>
		</div>
	</form>
</template>

<script>
import BasePasswordField  from '../form/base-fields/BasePasswordField';
import passwordValidation from '../../mixins/passwordValidation';

export default {
	mixins    : [passwordValidation],
	components: { BasePasswordField },
	props     : { submitPath: String, csrfToken: String }
}
</script>