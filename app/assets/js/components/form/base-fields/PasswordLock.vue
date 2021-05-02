<template>
	<span class="toggle__password"><i :class="iconClasses"></i></span>
</template>

<script>
export default {
	props: { iconClasses: String }
}
</script>