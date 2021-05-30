let strength = 0;
let validations = [];
const emailCheckPath = $('#email-check-path-data').attr('data-url');
const nameField = $('#register__name');
const emailField = $('#register__email');
const emailAvailabilityField = $('.email__availability');
const passwordField = $('#register__plainPassword');
const togglePassword = $('.toggle__password');
const passwordToggleIcon = $('.toggle__password-icon');
const validationBar1 = $('.validation__bar-1');
const validationBar2 = $('.validation__bar-2');
const validationBar3 = $('.validation__bar-3');
const validationBar4 = $('.validation__bar-4');
const registerAgreeBox = $('#register__agreeToTerms');
const submitFormButton = $('#register__register');
const validatePassword = (e) => {
	validations = [
		(e.target.value.length > 12),
		(e.target.value.search(/[A-Z]/) > -1),
		(e.target.value.search(/[0-9]/) > -1),
		(e.target.value.search(/[$&+,:;=?@#]/) > -1),
	];
	strength = validations.reduce((acc, cur) => acc + cur);
	addClassesToBars();
	addClassesToValidations();
	if (strength > 3) {
		$(passwordField).addClass('feedback__valid');
		$(passwordField).removeClass('feedback__invalid');
	} else {
		$(passwordField).addClass('feedback__invalid');
		$(passwordField).removeClass('feedback__valid');
	}
	isFormValid();
};
const addClassesToBars = () => {
	strength > 0 ? validationBar1.addClass('bar__show') : validationBar1.removeClass('bar__show');
	strength > 1 ? validationBar2.addClass('bar__show') : validationBar2.removeClass('bar__show');
	strength > 2 ? validationBar3.addClass('bar__show') : validationBar3.removeClass('bar__show');
	strength > 3 ? validationBar4.addClass('bar__show') : validationBar4.removeClass('bar__show');
};
const addClassesToValidations = () => {
	for (let i = 0; i < validations.length; i++) {
		validations[i] ? validationPass(i) : validationDoesNotPass(i);
	}
}
const validationPass = (i) => {
	$(`.validation__${ i + 1 }-icon`).addClass('icon-check').removeClass('icon-exclamation');
}
const validationDoesNotPass = (i) => {
	$(`.validation__${ i + 1 }-icon`).removeClass('icon-check').addClass('icon-exclamation');
}
const showPassword = () => {
	passwordField.prop('type', 'text');
	passwordToggleIcon.addClass('icon-lock-open').removeClass('icon-lock');
}
const hidePassword = () => {
	passwordField.prop('type', 'password');
	passwordToggleIcon.addClass('icon-lock').removeClass('icon-lock-open');
}
const validateEmail = () => {
	if (emailRegex()) {
		$.post(emailCheckPath, { '_email': emailField.val() })
		 .done(() => {
			 $(emailField).addClass('feedback__valid');
			 $(emailField).removeClass('feedback__invalid');
			 $(emailAvailabilityField).css('display', 'block');
			 $(emailAvailabilityField).removeClass('invisible');
			 $(emailAvailabilityField).text('Email is available.');
		 })
		 .fail(() => {
			 $(emailField).addClass('feedback__invalid')
			 $(emailField).removeClass('feedback__valid')
			 $(emailAvailabilityField).css('display', 'block');
			 $(emailAvailabilityField).removeClass('invisible');
			 $(emailAvailabilityField).text('Email is not available.');
		 });
	}
	isFormValid();
};
const emailRegex = () => {
	return new RegExp(
		/(?:[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])/)
		.test(emailField.val());
}
const validateName = () => {
	if ($(nameField).val().length > 0 && nameRegex()) {
		$(nameField).addClass('feedback__valid');
		$(nameField).removeClass('feedback__invalid');
	} else {
		$(nameField).addClass('feedback__invalid');
		$(nameField).removeClass('feedback__valid');
	}
	isFormValid();
}
const nameRegex = () => {
	return new RegExp(
		/^[a-zA-Z]+(([',. -][a-zA-Z ])?[a-zA-Z]*)*$/
	).test(nameField.val());
}
$('form').submit(function (evt) {
	if (! isFormValid()) {
		evt.preventDefault();
	}
	$('form').submit();
});
const isFormValid = () => {
	if (
		$(emailField).attr('class').includes('feedback__valid')
		&& $(nameField).attr('class').includes('feedback__valid')
		&& $(passwordField).attr('class').includes('feedback__valid')
		&& $(registerAgreeBox).is(':checked')
	) {
		$(submitFormButton).removeAttr('disabled');
		return true;
	}
	$(submitFormButton).attr('disabled', true);
	return false;
}
togglePassword.on('mouseenter', showPassword).on('mouseleave', hidePassword);
passwordField.on('input', validatePassword);
emailField.on('blur', validateEmail);
nameField.on('blur', validateName);
registerAgreeBox.on('change', isFormValid);