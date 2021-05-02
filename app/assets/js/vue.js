import Vue from 'vue';

window.axios = require('axios').default;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

let token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
	window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
	console.error('CSRF token not found!!!!');
}

Vue.component('RegisterForm', require('./components/security/RegisterForm').default);
Vue.component('LoginForm', require('./components/security/LoginForm').default);
Vue.component('PasswordChangeWithTokenForm', require('./components/security/PasswordChangeWithTokenForm').default);

new Vue({
			el  : '#VueApp',
			data: {}
		});