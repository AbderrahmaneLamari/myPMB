<template>
	<div class="rmc_search_sub_fields">
		<div v-for="(field, key) in fields" :key="key">
			<label v-if="field.COMMENT" :for="computedId(field.NAME, key)">{{ field.COMMENT }}</label>
			<template v-if="field.TYPE == 'input'">
				<input :id="computedId(field.NAME, key)" v-if="field.INPUT_TYPE == 'checkbox'" type="checkbox" :name="computedName(field.NAME)" :value="field.VALUE">
				<input :id="computedId(field.NAME, key)" v-if="field.INPUT_TYPE == 'number'" type="number" :name="computedName(field.NAME)" :value="field.VALUE">
				<input :id="computedId(field.NAME, key)" v-if="field.INPUT_TYPE == 'hidden'" type="hidden" :name="computedName(field.NAME)" :value="field.VALUE">
			</template>
		</div>
	</div>
</template>

<script>
export default {
	name: "fieldvars",
	props : ['index', 'fieldId', 'fields'],
	methods: {
	    computedName: function(name) {
	        return `fieldvar_${this.index}_${this.fieldId}[${name}][]`;
	    },
	    computedId: function(name, index) {
	        return `fieldvar_${this.index}_${this.fieldId}_${name}_${index}`;
	    }
	    
	}
}
</script>