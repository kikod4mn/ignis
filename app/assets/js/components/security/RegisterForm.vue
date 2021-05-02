<template>
	<form :action="submitPath" method="POST" class="material__form">
		<input id="register__token" type="hidden" name="register[_token]" :value="csrfToken" />
		
		<div class="form-group">
			<base-text-field
				id="register-name" type="text" name="register[_name]" :value="nameValue" required="required" label="Name"
				@input="validateForm()"
			></base-text-field>
		</div>
		
		<div class="form-group">
			<base-text-field
				id="register-email" type="email" name="register[_email]" v-model="email" required="required" label="Email" :classes="emailFieldClasses()"
				@input="validateForm()"
			></base-text-field>
			<p :class="emailAvailabilityClasses()">{{ emailAvailabilityText() }}</p>
		</div>
		
		<div class="form-group">
			<base-password-field
				id="register-password" name="register[_plainPassword]" label="Password" required="required"
				@input.native="validateForm($event.target.value)"
			></base-password-field>
		</div>
		
		<div class="form-group">
			<base-check-box-field
				id="AgreeToTerms" name="register[_agreeToTerms]" label="Agree to terms I for sure read" required="required"
			></base-check-box-field>
		</div>
		
		<div class="form-group mb-3 row">
			<div class="col-6">
				<button id="Register" :class="submitButtonClasses()" type="submit">Register</button>
			</div>
			<div class="col-6">
				<a :href="forgotPasswordPath" class="btn btn-outline-secondary w-100">Forgot password?</a>
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
import BaseTextField      from '../form/base-fields/BaseTextField';
import BasePasswordField  from '../form/base-fields/BasePasswordField';
import BaseCheckBoxField  from '../form/base-fields/BaseCheckBoxField';
import passwordValidation from '../../mixins/passwordValidation';

export default {
	mixins    : [passwordValidation],
	props     : {
		submitPath         : String,
		csrfToken          : String,
		nameValue          : String,
		emailValue         : String,
		emailValidationLink: String,
		forgotPasswordPath : String
	},
	components: { BaseCheckBoxField, BasePasswordField, BaseTextField },
	data      : function () {
		return {
			isEmailAvailable: false,
			isEmailValid    : false,
			emailMessage    : '',
			email           : this.emailValue,
		}
	},
	methods   : {
		emailAvailabilityClasses() {
			let classes = 'email__availability';
			if (! this.isEmailAvailable) classes += ' invisible';
			return classes;
		},
		emailAvailabilityText() {
			return this.emailMessage;
		},
		emailFieldClasses() {
			return this.isEmailAvailable ? 'material__input available' : 'material__input un-available';
		},
		emailAvailabilityCheck() {
			let email = this.email.trim();
			this.isEmailValid = email.length !== 0;
			if (email.length > 2 && this.emailRegex()) {
				axios.post(this.emailValidationLink, { email: email })
				     .then(response => {
					     if (response.status === 200) {
						     this.isEmailAvailable = true;
						     this.emailMessage = response.data.message;
					     } else {
						     this.isEmailAvailable = false;
						     this.emailMessage = response.data.message;
					     }
				     })
				     .catch(response => {
					     this.isEmailAvailable = false;
					     this.emailMessage = response.data.message;
				     })
			}
		},
		validateForm(pwd = '') {
			let email = this.emailValue.trim();
			if (email.length > 2 && this.emailRegex()) {
				this.emailAvailabilityCheck();
			}
			if (pwd.trim().length > 0) {
				this.validatePassword(pwd);
			}
		},
		submitButtonClasses() {
			// if (this.isEmailValid && this.isEmailAvailable && this.strength > 3) {
			// 	return 'btn btn-primary w-100';
			// }
			// return 'btn btn-primary w-100 disabled';
			return 'btn btn-primary w-100';
		},
		emailRegex() {
			return new RegExp(
				/(?:[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])/)
				.test(this.email)
		}
	}
}
</script>