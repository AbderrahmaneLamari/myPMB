import Vue from "vue";
import formSearch from "./components/formSearch.vue";

new Vue({
	el:"#search",
	data: {
		pmb: pmbDojo.messages,
		formData: $data.formData
	},
	methods: {},
	components : {
		formsearch: formSearch
	}
});
