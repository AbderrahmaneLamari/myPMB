<template>
	<div id="registrationList" class="row">
		<h3>{{ pmb.getMessage("animation", "animation_empr_registration_title") }}</h3>
		<div class="registrationListContainer">
			<table>
				<tbody>
					<tr>
						<th>{{ pmb.getMessage("animation", "animation_empr_registration_animation_start") }}</th>
						<th>{{ pmb.getMessage("animation", "animation_empr_registration_animation_title") }}</th>
						<th>{{ pmb.getMessage("animation", "animation_empr_registration_nb_registred") }}</th>
						<th>{{ pmb.getMessage("animation", "animation_empr_registration_status") }}</th>
					</tr>
					<tr v-for="(registration, index) in registrations" :class="index % 2 ? 'even' : 'odd'">
						<td>
							{{ registration.animation.event.startDate }}
							<template v-if="registration.animation.event.startHour != '00:00'"> - {{ registration.animation.event.startHour }}</template>
						</td>
						<td><a :href="'./index.php?lvl=animation_see&id=' + registration.animation.id">{{ registration.animation.name }}</a></td>
						<td>{{ registration.nbRegisteredPersons }}</td>
						<td>{{ registration.registrationStatus.name }}</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</template>

<script>
	export default {
		props : ["pmb", "registrations"]
	}
</script>