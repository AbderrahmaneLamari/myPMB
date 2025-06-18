// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: SearchUniverseController.js,v 1.18.2.3 2021/12/14 10:49:40 gneveu Exp $


define(["dojo/_base/declare",
    "dojo/topic",
    "dojo/_base/lang",
    "dojo/dom",
    "dojo/dom-construct",
    "dojo/dom-style",
    "dojo/on",
    "dojo/query",
    "dojo/dom-class",
    "dojo/dom-form",
    "dojo/dom-attr",
    "dojo/request/xhr",
    'dojo/_base/lang',
    'dojo/io-query',
    ], function (declare, topic, lang, dom, domConstruct, domStyle, on, query, domClass, domForm, domAttr, xhr, lang, ioQuery) {

    return declare(null, { 
    	memoryNodes : null,
    	links: null,
    	search_field: null,
    	selectedLink: null,
    	universeQuery:null,
    	segmentsValues: null,
    	constructor: function(universeQuery){
    		this.search_field = document.getElementsByName('user_query')[0];
    		this.links = query('.search_universe_segments_row');
			this.addUniverseEvents();
			this.segmentsValues = new Array();
			this.displayNbResultsInSegments();
			
    	},
    	removeSelected: function(){
    		this.links.forEach(link => {
    			domClass.remove(link, 'selected');
    		});
    	},
    	setWaitingIcon: function(){
    		this.links.forEach(link => {
    			var resultP = query('.segment_nb_results', link)[0];
    			resultP.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
    		});
    	},
    	setUniverseHistory : function(data) {
    		var promise = new Promise(lang.hitch(this, function(resolve, reject){
    			if (data.user_query) {
    				dom.byId('last_query').value = data.user_query.replace(/'/g, "\'");
    			}
	    		xhr("./ajax.php?module=ajax&categ=search_universes&sub=search_universe&action=rec_history&id="+data.universe_id,{
					data : data,
					handleAs: "json",
					method:'POST',
				}).then((response)=>{
					if (response) {
						var historyNode = dom.byId('search_index');
						if (historyNode && response.search_index) {
							historyNode.value = response.search_index;
						}
						this.links.forEach(segment => {
							this.updateSegmentsLinks(segment);
						});
						resolve(true);
					}
				});
    		}));
    		return promise;
    	},
    	addUniverseEvents: function(){
    		var form = dom.byId('search_universe_input');
    		if (form) {
				on(form, 'submit', lang.hitch(this, function(e){
					e.preventDefault();
					e.stopPropagation();
					this.universeFormSubmit(form);
				}));
    		}

			//si on a une valeur par défaut (provenant de l'historique), on poste les formulaires des segments;			
			var last_query = dom.byId('last_query').value;
			if (last_query) {
				//this.setUserQuery(last_query);
				this.universeFormSubmit(form);
			}
    	},
    	
    	setUserQuery : function(newValue) {
    		var user_query = dom.byId('user_query');
    		if (user_query) {
    			user_query.value = newValue; 
    		}
    	},
    	
    	universeFormSubmit : function (form, setHistory = true) {
			var defaultSegment = dom.byId('default_segment').value;
			var data = JSON.parse(domForm.toJson(form.id));
			this.setWaitingIcon();
			if (setHistory) {
				var promise = this.setUniverseHistory(data);
			}else{
				var promise = new Promise(function(resolve, reject){resolve(true);});
			}
			
			promise.then(lang.hitch(this,function(){
				/**
				 * Si il y a un segment par defaut
				 * on affiche la page du segement.
				 */
				var last_query = dom.byId('last_query').value;
				if (parseInt(defaultSegment) && last_query && last_query != "*" ){
					var default_segment_url = dom.byId("default_segment_url");
					if (default_segment_url && default_segment_url.value) {
						form.action = default_segment_url.value;
						form.submit();
					}
					return true;
				}
			}));
			
			var user_query = dom.byId('last_query').value;
			var universe_user_rmc = dom.byId('universe_user_rmc');
//			
			if (universe_user_rmc && universe_user_rmc.value) {
				user_query = universe_user_rmc.value;
			}
			
			this.links.forEach(link => {
				var segmentId = domAttr.get(link, 'data-segment-id');
				var universeId = domAttr.get(link, 'data-universe-id');
				var resultP = query('.segment_nb_results', link)[0];
				
				var storage_user_query = sessionStorage.getItem('universe_'+universeId+'_segment_'+segmentId+'_query_'+user_query);
				
				if(sessionStorage.getItem('universe_'+universeId+'_segment_'+segmentId+'_nb_'+user_query) != null && ( storage_user_query != null && storage_user_query == user_query )){
					var resultP = query('.segment_nb_results', link)[0];
					resultP.innerHTML = '('+sessionStorage.getItem('universe_'+universeId+'_segment_'+segmentId+"_nb_"+user_query)+')';
					
					sessionStorage.setItem('universe_'+universeId+'_segment_'+segmentId+'_last_query', sessionStorage.getItem('universe_'+universeId+'_segment_'+segmentId+"_nb_"+user_query));
				} else {
					data.segment_id = domAttr.get(link, 'data-segment-id');
					xhr(form.action,{
						data : data,
						handleAs: "json",
						method:'POST',
					}).then(lang.hitch(this,function(response){
						if (response) {
							resultP.innerHTML = '('+response.nb_result+')';
							sessionStorage.setItem('universe_'+universeId+'_segment_'+segmentId+"_nb_"+user_query, response.nb_result);
							sessionStorage.setItem('universe_'+universeId+'_segment_'+segmentId+'_query_'+user_query, user_query);
							
							sessionStorage.setItem('universe_'+universeId+'_segment_'+segmentId+'_last_query', response.nb_result);
						}
					}));
				}
			});
			if (universe_user_rmc && universe_user_rmc.value) {
				document.location = "#search_universe_segments_list";
			}
    	},
    	
    	displayResult: function(){
	    	dom.byId('search_universe_result_container').innerHTML = '';
			if(this.segmentsValues.results){
				dom.byId('search_universe_result_container').innerHTML = '<h3>'+this.segmentsValues.label+'</h3>'+this.segmentsValues.results;
				collapseAll();
			}
    	},
    	
    	updateSegmentsLinks : function(segment) {
			var searchIndex = dom.byId('search_index').value;
			if (segment) {
				var segmentLink = query('a', segment)[0];
				var url = domAttr.get(segmentLink, "href");
				var searchParams = url.substring(url.indexOf("?") + 1);
				var queryObject = ioQuery.queryToObject(searchParams);
				queryObject.search_index = searchIndex;
				url = url.split('?')[0]+"?"+ioQuery.objectToQuery(queryObject);

				domAttr.set(segmentLink, "href", url);
				
				
//				domAttr.get(segmentLink, "href");
//				domAttr.set(segmentLink, "href", domAttr.get(segmentLink, "href") + "&search_index=" + searchIndex);
    		}
    	},
    	
    	displayNbResultsInSegments : function() {
    		//attention en cas de rmc provenant des segments
    		var user_query = dom.byId('user_query');
    		var user_rmc = dom.byId('user_rmc');
    		var last_query = dom.byId('last_query').value;
    		
    		if ((!last_query) && (!user_rmc || !user_rmc.value) && (!user_query || !user_query.value)) {
	    		var form = dom.byId('search_universe_input');
				if (form) {
					on.emit(form, "submit", {
					    bubbles: true,
					    cancelable: true
				    });
				}
			}
    		if (user_query && user_query.value == "*") {
    			user_query.value = "";
    		}
    	},
    });
});