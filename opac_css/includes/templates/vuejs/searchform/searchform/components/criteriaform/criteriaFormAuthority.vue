<template>
	<div class="rmc_criteria_form_authority">
		<input v-if="operatorSelected != 'AUTHORITY'" :id="id+'_id_0'" :name="id+'[]'"  type="hidden" v-model="searchValue">
		<input v-else :id="id+'_id_0'" :name="id+'[]'" type="hidden" :value="searchValueId">
		
		<div :id="'d'+id+'_lib_'+index" class="ajax_completion" ></div>
		<select :id="opName" :name="opName" class="rmc_search_op" v-model="operatorSelected">
			<option v-for="(operator, key) in operators" :key="key" :value="operator.value">
				{{ operator.label }}
			</option>
		</select>
        <input
        	:id="id+'_lib_'+index"
       		:name="name"
       		class="rmc_search_authority rmc_search_txt"
       		type="text"
       		autocomplete="off"
       		:autfield="autfield"
       		:autid="autid"
       		:list="id+'_lib_'+index+'_datalist'"
       		v-model="searchValue"
			@input.prevent="e => updateDataList(e)"
		>
		<datalist :id="id+'_lib_'+index+'_datalist'">
			<option v-for="element in dataList" :data-entity_id="element.value">{{ element.label }}</option>
		</datalist>
        <!-- <fieldvars :fields="criteria.VAR" :field="criteria_id" :index="index"></fieldvars>-->
    </div>
</template>
<script>
import fieldvars from "./fieldvars.vue";

export default {
	name: "criteriaFormAuthority",
	props : ['criteria', 'searchData', 'index', 'showfieldvars'],
	data: function () {
		return {
			selectorValue: "",
			searchValue: "",
			dataList: [],
			operatorSelected: 'AUTHORITY',
	        searchValueId: ""

		}
	},
	components : {
	    fieldvars,
	},
	created : function() {
    	if(this.searchData[this.index] && this.searchData[this.index].OP){
            for (var i = 0; i < this.criteria.QUERIES.length; i++) {
                var operator = this.criteria.QUERIES[i];
                if (this.searchData[this.index].OP == operator['OPERATOR']) {
                	this.operatorSelected = this.searchData[this.index].OP;
                }
            }
    	}
    	
       	if(this.searchData[this.index] && this.searchData[this.index].FIELD){
	       	if(this.searchData[this.index] && this.searchData[this.index].FIELDLIB){
	       		this.searchValue = this.searchData[this.index].FIELDLIB[0];
	       	} else {
	       		this.searchValue = this.searchData[this.index].FIELD[0];
	       	}
	       	if(this.operatorSelected == 'AUTHORITY'){
	       		this.searchValueId = this.searchData[this.index].FIELD[0];
	       	}
       	}
       	
	},
	computed: {
        name: function() {
            return `field_${this.index}_${this.criteria.FIELD_ID}_lib[]`;
        },
        autfield: function() {
        	return `field_${this.index}_${this.criteria.FIELD_ID}_id_0`;
        },
        autid: function() {
        	return `field_${this.index}_${this.criteria.FIELD_ID}_id_0`;
        },
        id: function() {
        	return `field_${this.index}_${this.criteria.FIELD_ID}`;
        },
        opName: function() {
        	return `op_${this.index}_${this.criteria.FIELD_ID}`;
        },
        operators: function() {
	        var operators = new Array();
	        if (this.criteria.QUERIES && this.criteria.QUERIES.length) {
	            for (var i = 0; i < this.criteria.QUERIES.length; i++) {
	                var operator = this.criteria.QUERIES[i];
	                if (operator) {
		                operators.push({value: operator['OPERATOR'], label: operator['LABEL']});
	                }
	            }
	        }
	        return operators;
	    },
    },
	mounted : function() {
		this.authoritiesAjaxParse(this.criteria.INPUT_TYPE);
	},
	methods : {
		authoritiesAjaxParse : function (input_type) {
			ajax_parse_dom();
		},
		updateDataList: function(event){
			var linkValue = false;
			if(event.target.value){
				for(var element of this.dataList){
					if(event.target.value == element.label){
						this.searchValueId = element.value;
						linkValue = true;
						this.operatorSelected = 'AUTHORITY';
						break
					}
				}
			}
			
			var formData = new FormData();
			formData.append("handleAs", "json");
			formData.append("completion", this.criteria.INPUT_OPTIONS.AJAX);
			formData.append("autexclude", "");
			formData.append("param1", "");
			formData.append("param2", 1);
			formData.append("rmc_responsive", 1);
			
			var data = this.searchValue;
			if (!data) {
				data = "*";
			}
			formData.append("datas", data);
			
			fetch("./ajax_selector.php", {
				method: 'POST',
				body: formData
			}).then((response)=> {
				if (response.ok) {
					response.json().then((result)=> {
						this.setDatalist(result);
				    });
				} else {
					console.error("Error search special ajax");
				}
			}).catch((error) => {
				console.error(error.message);
			});
		},
		setDatalist: function(data) {
			this.dataList = data;
		}
	}
}
</script>