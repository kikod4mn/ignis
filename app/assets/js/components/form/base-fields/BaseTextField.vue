<template>
	<div class="material__field">
		<input
			@input="$emit('input', $event.target.value)"
			:id="id" :type="type" :name="name" :value="value" :class="classList()" placeholder="" :required="required" />
		<label :for="id" class="material__label">{{ label }}</label>
	</div>
</template>

<script>
export default {
	props  : { id: String, type: String, name: String, value: String, label: String, required: String, classes: String },
	methods: {
		classList() {
			return this.classes ? this.classes : 'material__input';
		}
	}
}
</script>