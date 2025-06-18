<template>
	<div class="container">
		<div v-if="field.options[0].repeatable[0].value" class="row">
			<input @click="$parent.addCustomMultipleField()" class="bouton" type="button" value="+"/>
		</div>
		<div v-for="(customValue, index) in values" class="row">
			<input :id="field.name + index" v-model="customValue.value" type="text" :size="field.options[0].size[0].value" :maxlength="field.options[0].maxsize[0].value"/>
			<input @click="$parent.deleteCustomMultipleField(index)" class="bouton" type="button" value="X"/>
			<input v-if="field.options[0].repeatable[0].value && index == values.length - 1" @click="$parent.addCustomMultipleField()" class="bouton" type="button" value="+"/>
		</div>
	</div>
</template>

<script>
	export default {
		props : ["field", "values"]
	}
</script>