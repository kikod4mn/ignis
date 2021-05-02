export default {
	data   : function () {
		return {
			password       : '',
			validations    : [],
			strength       : 0
		}
	},
	methods: {
		validatePassword(pwd) {
			this.password = pwd;
			this.validations = [];
			this.validations.push(pwd.length > 12);
			this.validations.push(pwd.search(/[A-Z]/) > -1);
			this.validations.push(pwd.search(/[0-9]/) > -1);
			this.validations.push(pwd.search(/[$&+,:;=?@#]/) > -1);
			this.strength = this.validations.reduce((acc, cur) => acc + cur);
		},
		barClasses(barNumber, addClass = '') {
			return `validation__bar validation__bar--${ barNumber } ${ addClass }`;
		},
		validationMarkClasses(markNumber, addClass = 'icon-exclamation') {
			return `validation__${ markNumber }--icon ${ addClass }`;
		},
		bar1() {
			return this.strength > 0 ? this.barClasses(1, 'bar__show') : this.barClasses(1);
		},
		bar2() {
			return this.strength > 1 ? this.barClasses(2, 'bar__show') : this.barClasses(2);
		},
		bar3() {
			return this.strength > 2 ? this.barClasses(3, 'bar__show') : this.barClasses(3);
		},
		bar4() {
			return this.strength > 3 ? this.barClasses(4, 'bar__show') : this.barClasses(4);
		},
		validation1() {
			return this.validations[0] ? this.validationMarkClasses(1, 'icon-check') : this.validationMarkClasses(1)
		},
		validation2() {
			return this.validations[1] ? this.validationMarkClasses(2, 'icon-check') : this.validationMarkClasses(2)
		},
		validation3() {
			return this.validations[2] ? this.validationMarkClasses(3, 'icon-check') : this.validationMarkClasses(3)
		},
		validation4() {
			return this.validations[3] ? this.validationMarkClasses(4, 'icon-check') : this.validationMarkClasses(4)
		},
	}
}