<template>
    <div id="dsi-form-sub-selector">
        <div class="dsi-form-group">
            <label class="etiquette" for="selectorList">{{ messages.get('dsi', 'source_form_subselector') }}</label>
            <div class="dsi-form-group-content">
                <select id="selectorList" name="selectorList" v-model="selector.subselector.namespace" @change="reset" required>
                    <option value="" disabled>{{ messages.get('dsi', 'source_form_default_subselector') }}</option>
                    <option v-for="(subSelectorItem, index) in subSelectorList" :key="index" :value="subSelectorItem.namespace">
                        {{ subSelectorItem.name }}
                    </option>
                </select>
            </div>	
        </div>	
        <RMCForm v-if="isRMC" rmc_type="notice" entity_type="record" @updateRMC="updateRMC" :search="selector.subselector.data.search" :human="selector.subselector.data.human_query"></RMCForm>
    </div>
</template>

<script>
	import RMCForm from "../../../components/RMCForm.vue";
	export default {
		props : ["selector"], 
		components : {
			RMCForm
		},
		data: function () {
			return {
				subSelectorList: [],
			}
		},
		created: function() {
			this.getSubSelectorList();
		},
		methods: {
			getSubSelectorList: async function() {
				if(this.selector.namespace != "") {
					let response = await this.ws.get('Items', 'getSelectorList/' + encodeURI(this.selector.namespace));
					if (response.error) {
						this.notif.error(this.messages.get('dsi', response.errorMessage));
					} else {
						this.subSelectorList = response;
					}
				}
			},
            updateRMC: function(data) {
				this.$set(this.selector.subselector, "data", data);
            },
			reset: function() {
				this.selector.subselector.data = "";
			}
		},
		computed: {
			isRMC: function() {
				const TypeRMC = 7;
				if(this.selector.subselector.namespace != 0) {
					if(this.selector.subselector.namespace.split("\\")[TypeRMC] === "RMC") {
						return true;
					}
				}
				return false;
			}
		}
	}
</script>