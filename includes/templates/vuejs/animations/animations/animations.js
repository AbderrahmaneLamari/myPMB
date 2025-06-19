import Vue from "vue";
import animations from "./components/animations.vue";
import animationsform from "./components/animationsForm.vue";
import animationsview from "./components/animationsView.vue";
import animationsdnd from "./components/animationsDnD.vue";
import animationsdaughterlist from "./components/animationsDaughterList.vue";
import registration from "../registration/components/registration.vue";
import mailingsendlist from "../mailing/components/mailingSendList.vue";
import animationcalendar from "./components/animationCalendar.vue";

import { VueNestable, VueNestableHandle } from 'vue-nestable';

Vue.component('VueNestable', VueNestable);
Vue.component('VueNestableHandle', VueNestableHandle);

new Vue({
	el : "#animations",
	data : {
		animations : $data.animations,
		animationDaugthterList : $data.animationDaughterList,
		action : $data.action,
		formdata : $data.formData,
		pmb : pmbDojo.messages,
		registrationList : $data.registrationList,
		registrationWaitingList : $data.registrationWaitingList,
		animationList : $data.animationList,
		mailingSendList : $data.mailingSendList,
		calendarAnimation : [],
	},
	components : {
		animations : animations,
		animationsform : animationsform,
		animationsview : animationsview,
		animationsdnd : animationsdnd,
		animationsdaughterlist : animationsdaughterlist,
		registration : registration,
		mailingsendlist : mailingsendlist,
		animationcalendar : animationcalendar
	}
});