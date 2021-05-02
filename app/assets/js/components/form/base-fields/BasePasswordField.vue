<template>
	<div class="material__field">
		<base-text-field :id="id" :type="isPwdVisible" :name="name" class="material__input" v-model="password"
		                 placeholder="" :required="required" :label="label"
		></base-text-field>
		<password-lock @mouseover.native="hover = true" @mouseleave.native="hover = false" :iconClasses="iconClasses"></password-lock>
	</div>
</template>

<script>
import BaseTextField from './BaseTextField';
import PasswordLock  from './PasswordLock';

export default {
	components: { BaseTextField, PasswordLock },
	props     : { id: String, name: String, label: String, required: String },
	data() {
		return {
			password: '',
			hover   : false
		}
	},
	computed: {
		iconClasses : function () {
			if (this.hover) {
				return 'toggle_password--icon icon-lock-open';
			}
			return 'toggle_password--icon icon-lock';
		},
		isPwdVisible: function () {
			if (this.hover) {
				return 'text';
			}
			return 'password';
		}
	}
}
</script>