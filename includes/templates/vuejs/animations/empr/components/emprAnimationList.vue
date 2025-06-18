<template>
	<div id="animationList">
		<div class='row'>
			<hr>
		</div>
		<div class='row'>
			<h3>{{ pmb.getMessage("animation", "empr_registration_list") }}</h3>
		</div>
		<div class='row'>
			<table class='sortable'>
		    	<thead>
		        	<tr>
		            	<th>{{ pmb.getMessage("animation", "empr_registration_list_animation") }}</th>
		            	<th>{{ pmb.getMessage("animation", "empr_registration_list_animation_start_date") }}</th>
		            	<th>{{ pmb.getMessage("animation", "empr_registration_list_animation_end_date") }}</th>
		            	<th>{{ pmb.getMessage("animation", "list_animation_location") }}</th>
		            	<th>{{ pmb.getMessage("animation", "empr_registration_list_animation_price") }}</th>
		            	<th>{{ pmb.getMessage("animation", "empr_registration_list_animation_available_places") }}</th>
		            	<th></th>
		            </tr>
		        </thead>
		       	<tbody>
		       		<template v-if="registrationsList.length > 0">
			        	<tr v-for="(registration, index) in registrationsList" :key="index" :class="[ index%2 == 0 ? 'odd' : 'even', index == hover ? 'surbrillance' : '' ]"  @mouseover="hover = index " @mouseout="hover = -1 ">
			            	<td><a :href='animationLink(registration.animation)'>{{ registration.animation.name }}</a></td>
			            	<td>{{ registration.animation.event.startDate }} {{ registration.animation.event.startHour }}</td>
			            	<td>{{ registration.animation.event.endDate }} {{ registration.animation.event.endHour }}</td>
			            	<td>
			            		<template v-for="location in registration.animation.location">
			            			{{ location.locationLibelle }}<br>
			            		</template>
			            	</td>
			            	<td>
			            		<template v-for="price in registration.animation.prices">
			            			{{ price.name }} : {{ price.value }}<br>
			            		</template>
			            	</td>
			            	<td>
			            		<template v-if="registration.animation.hasChildrens">
			            			{{ pmb.getMessage("animation", "empr_registration_list_animation_places_NA") }}
			            		</template>
			            		<template v-else-if="!registration.animation.allQuotas.global || registration.animation.allQuotas.global == 0">
			            			{{ pmb.getMessage("animation", "empr_registration_list_animation_illimited_places") }}
			            		</template>
			            		<template v-else>
			            			{{ registration.animation.availableQuotas.global }} / {{ registration.animation.allQuotas.global }}
			            		</template>
			            	</td>
			            	<td>
			            		<input type="button" class='bouton' :value="pmb.getMessage('animation', 'animation_edit_registration')" @click="editRegistration(registration)">
			            	</td>
			            </tr>
		       		</template>
		       		<template v-else>
			        	<tr>
			            	<td colspan='7' class='center'>
			            		{{ pmb.getMessage("animation", "empr_registration_list_empty") }}
		            		</td>
			            </tr>
		       		</template>
		        </tbody>
		    </table>
		</div>
	</div>
</template>

<script>
	export default {
		props : ["pmb", "formdata"],
		
		data: function () {
			return {
			    registrationsList: [],
				hover : -1
			}
		},
		mounted : function() {
		    
		    let url = "./ajax.php?module=animations&categ=registration&action=emprList";
		    
			var data = new FormData();
			data.append('data', JSON.stringify({
			    emprId: this.formdata.emprId
			}));
			
			fetch(url, {
				method: 'POST',
				body: data
			}).then((response) => {
				if (response.ok) {
					response.text().then((reponse) => {
					    if (reponse && JSON.parse(reponse) != "") {
						    this.registrationsList = JSON.parse(reponse);
					    }
				    });
				} else {
					console.log(this.pmb.getMessage('animation', 'admin_animation_no_response'));
				}
			}).catch((error) => {
				console.log(this.pmb.getMessage('animation', 'admin_animation_error_fetch') + error.message);
			});
		},			
		methods: {
		    animationLink: function (animation) {
		        return './animations.php?categ=animations&action=view&id='+animation.idAnimation;
		    },
			editRegistration: function (registration) {
		        document.location='./animations.php?categ=registration&action=edit&id='+registration.idRegistration
		    }
		}
	}
</script>