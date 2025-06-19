import Vue from "vue";
import sort from "./components/sortComponent.vue";
import loader from "./components/LoaderComponent.vue";

var facettes_cache = {};

window.openModal = function (id) {
	window.dispatchEvent(new CustomEvent("open_modal", {detail: {facettes: facettes_cache[id], id : id}}));
}

window.closeModal = function () {
	window.dispatchEvent(new CustomEvent("close_modal"));
}

window.callback_see_more_modal = function (id, table, response) {
	try {		
		const facettes_ajax = JSON.parse(response) ?? [];
		
		var facettes = [];
		var tr_list = document.querySelectorAll(`#facette_list_${id} .facette_tr`);
		for (var i = 0; i < tr_list.length; i++ ) {
			
			const tr = tr_list[i];
			var facette = {
		      "facette_libelle": "",
		      "facette_number": 0,
		      "facette_id": "",
		      "facette_value": "",
		      "facette_link": ""
		   };
		   
			var check_facette_node = tr.querySelector('input[name="check_facette[]"]');
			if (check_facette_node && check_facette_node.value) {
				facette.facette_value = check_facette_node.value.trim();
			}

			var facette_libelle_node = tr.querySelector('.facette_libelle');
			if (facette_libelle_node) {
				facette.facette_libelle = facette_libelle_node.innerText.trim();
			}

			var facette_number_node = tr.querySelector('.facette_number');
			if (facette_number_node) {
				var nb = facette_number_node.innerText.replaceAll(/[^0-9]+/g, '');
				facette.facette_number = parseInt(nb);
			}

			var facette_link_node = tr.querySelector('.facet-link');
			if (facette_link_node && facette_link_node.href) {
				facette.facette_link = facette_link_node.href;
			}
			
			facettes.push(facette)
		}
		
		// On supprime la ligne '+'
		table.tBodies[0].removeChild(table.rows[table.rows.length-1]);
		// On ajoute la ligne '-'
		add_see_less(table, id);
		
		facettes_cache[id] = [...facettes, ...facettes_ajax];
		
		openModal(id);
	} catch(e) {
		console.error(e);
	}
}

window.addEventListener("load", () => {
	Vue.prototype.getMessage = (code) => {
		return pmbDojo.messages.getMessage('facettes_modal', code);
	};
	
	new Vue({
		el: "#facettes_modal",
		data: {
			...$modalData,
			showModal: false,
			events: {},
			facettes : [],
			selectedFacettes : [],
			id : 0
		},
		components: {
			sort,
			loader
		},
		mounted: function() {
			this.events = {
				"open_modal": (event) => this.open(event),
				"close_modal": (event) => this.close(event)
			};
			
			for (let event in this.events) {				
				window.addEventListener(event, this.events[event]);
			}
		},
		destroy: function() {
			for (let event in this.events) {				
				window.removeEventListener(event, this.events[event]);
			}
		},
		computed : {
			facetteLabel : function() {
				if(document.querySelector(`#facette_list_${this.id} th`)){
					return document.querySelector(`#facette_list_${this.id} th`).innerText;
				}
				return "";
			},
			description : function() {
				const description = this.getMessage(`description_facette_${this.id}`);
				return (description) ? description : this.getMessage('description_facette')
			}
		},
		methods: {
			closeModal: function() {
				window.closeModal();
			},
			close: function() {
				this.selectedFacettes = [];
				this.showModal = false;
			},
			open: function(event) {
				if (event.detail && event.detail.facettes) {
					this.facettes = event.detail.facettes;
				}
				if (event.detail && event.detail.id) {
					this.id = event.detail.id;
				}
				this.showModal = true;
			},
			check : function(facettes) {
				this.selectedFacettes = facettes;
			},
			valid : function() {
				this.showLoader();
				var form = document.querySelector(`form[name="${this.form_name}"]`);
				if(form){
					for(let facette of this.selectedFacettes) {
						let input = document.createElement("input");
						input.type = "hidden";
						input.value = this.formatString(facette);
						input.name = "check_facette[]";
						form.appendChild(input);
					}
					form.submit();
				} else {
					console.error('No form');
					this.hiddenLoader();
				}
			},
			facetteClicked : function(facette) {
				this.selectedFacettes = [facette];
				this.valid();
			},
			formatString : function (encodedStr) {
	            var parser = new DOMParser();
	            // convertie les "&eacute;" en "é", etc.
	            var dom = parser.parseFromString(encodedStr, 'text/html');
	            // remplace les multiples espaces en 1 seul
	            var str = dom.body.textContent.replace(/(\s){2,}/gm, ' ');
	            return str.trim();
	        }
		}
	});
});