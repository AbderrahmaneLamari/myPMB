<template>
	<div id="registrationSaved" class="row">
		<h2 class="registration_intro">{{ pmb.getMessage("animation", "animation_registration_saved_success") }}</h2>
		
		<p>{{ pmb.getMessage("animation", "animation_registration_recap") }}</p>
		
		<div class="row" v-for="(registration, index) in formdata.registrationList" :key="index">
			
			<!-- contact -->
			<hr />
			<div class='row'>
				<h3>{{ pmb.getMessage("animation", "animation_registration_contact") }}</h3>
			</div>
			<table class="fiche-inscription">
				<tr>
					<td class="bg-grey align_right">
						<span class="etiq_champ">{{ pmb.getMessage("animation", "animation_registration_barcode") }}</span>
					</td>
					<td v-if="registration.barcode">{{ registration.barcode}}</td>
					<td v-else><em>{{ pmb.getMessage("animation", "animation_registration_not_set") }}</em></td>
				</tr>
				<tr>
					<td class="bg-grey align_right">
						<span class="etiq_champ">{{ pmb.getMessage("animation", "animation_registration_name") }}</span>
					</td>
					<td>{{ registration.name}}</td>
				</tr>
				<tr>
					<td class="bg-grey align_right">
						<span class="etiq_champ">{{ pmb.getMessage("animation", "animation_registration_email") }}</span>
					</td>
					<td v-if="registration.email"><a :href="'mailto:'+registration.email">{{ registration.email }}</a></td>
					<td v-else><em>{{ pmb.getMessage("animation", "animation_registration_not_set") }}</em></td>
				</tr>
				<tr>
					<td class="bg-grey align_right">
						<span class="etiq_champ">{{ pmb.getMessage("animation", "animation_registration_phone") }}</span>
					</td>
					<td>{{ registration.phoneNumber }}</td>
				</tr>
			</table>
			
			<!-- personnes inscrites -->
			<hr />
			<div class='row'>
				<h3>{{ pmb.getMessage("animation", "animation_registration_persons") }} {{ registration.registrationListPerson.length }}</h3>
			</div>
			<br>
			<table>
				<thead>
					<tr>
						<th>{{ pmb.getMessage("animation", "animation_registration_barcode") }}</th>
						<th>{{ pmb.getMessage("animation", "animation_registration_name") }}</th>
						<th>{{ pmb.getMessage("animation", "animation_registration_price_type") }}</th>
					</tr>
				</thead>
				<tbody>
					<template v-for="(person, indexPerson) in registration.registrationListPerson">
						<tr>
							<td v-if="person.barcode">{{ person.barcode }}</td>
							<td v-else><em>{{ pmb.getMessage("animation", "animation_registration_not_set") }}</em></td>
							<td>{{ person.name }}</td>						
							<td>{{ getPrices(registration.animation, person.numPrice) }}</td>						
						</tr>
						<tr>
							<td colspan="3">
								<div class="row">
									<customfields :customfields="person.personCustomsFields" customprefixe="price_type" :img="formdata.img" :pmb="pmb"></customfields>
								</div>
							</td>
						</tr>
					</template>
				</tbody>
			</table>
			
			<!-- Animation -->
			<hr />
			<div class="row">
				<h3>{{ pmb.getMessage("animation", "animation") }} : <a :href="'./index.php?lvl=animation_see&id='+registration.animation.idAnimation">{{ registration.animation.name }}</a></h3>
			</div>
			<div class="row">
				<p>
					{{ pmb.getMessage("animation", "animation_registration_date") }} :
					<strong>{{ registration.animation.event.startDate }}</strong> {{ pmb.getMessage("animation", "animation_registration_date_to") }} <strong>{{ registration.animation.event.endDate }}</strong>
				</p>
				<div class="resume" v-html="registration.animation.description"></div>
			</div>
			
		</div>
	</div>
</template>

<script>
	import customfields from "../../../common/customFields/view/customFields.vue";
	export default {
		props : [
			"pmb",
			"formdata"
		],

		components : {
			customfields,
		},
		
		methods : {
		    getPrices: function (animation, numPrice) {
		        for (let price of animation.prices) {
		            if (price.idPrice == numPrice) {
		                return price.name + " : " + price.value + " " + this.formdata.globals.pmbDevise;
		            }
		        }
		    }
		}
	}
</script>