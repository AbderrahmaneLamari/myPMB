<template>
    <div class="rmc_criteria_form_map">
        <operators 
        	v-if="criteria.QUERIES" 
        	:field="criteria_id" 
        	:index="index" 
        	:queries="criteria.QUERIES" 
        	:selected="criteria.OPERATOR"
        	@changeOp="$emit('updateOperator', $event)"></operators>
    </div>
</template>

<script>
import operators from "./operators.vue";

export default {
    name: "criteriaFormMap",
    props : ['criteria', 'criteria_id', 'index', 'showfieldvars'],
    components : {
        operators
    }
}
</script>