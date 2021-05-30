let strength = 0;
let validations = [];
const passwordField = $('#change_password__plainPassword');
const togglePassword = $('.toggle__password');
const passwordToggleIcon = $('.toggle__password-icon');
const validationBar1 = $('.validation__bar-1');
const validationBar2 = $('.validation__bar-2');
const validationBar3 = $('.validation__bar-3');
const validationBar4 = $('.validation__bar-4');
const submitFormButton = $('#change_password__reset');
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
$(submitFormButton).submit(function (evt) {
	if (! isFormValid()) {
		evt.preventDefault();
	}
	$('form').submit();
});
const isFormValid = () => {
	if ($(passwordField).attr('class').includes('feedback__valid')) {
		$(submitFormButton).removeAttr('disabled');
		return true;
	}
	$(submitFormButton).attr('disabled', true);
	return false;
}
togglePassword.on('mouseenter', showPassword).on('mouseleave', hidePassword);
passwordField.on('input', validatePassword);