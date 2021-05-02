<template>
	<div class="material__checkbox text-center">
		<input class="" type="checkbox" :name="name" :id="id" :required="required">
		<label class="" :for="id">{{ label }}</label>
	</div>
</template>

<script>
export default {
	props: { id: String, name: String, label: String, required: String },
}
</script>