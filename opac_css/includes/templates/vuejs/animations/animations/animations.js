import Vue from "vue";
import animationssee from "./components/animationsSee.vue";
import animationslist from "./components/animationsList.vue";

new Vue({
	el : "#animations",
	data : {
		animations : $data.animations,
		action : $data.action
	},
	components : {
		animationssee : animationssee,
		animationslist : animationslist
	}
});