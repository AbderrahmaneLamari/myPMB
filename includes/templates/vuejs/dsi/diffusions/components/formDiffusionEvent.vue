<template>
	<div v-if="eventActive" class="dsi-form-diffusion-event">
        <fieldset class="dsi-form-diffusion-event-list">
            <legend class="dsi-legend-setting">{{ messages.get('dsi', 'event_form_triggers_list') }}</legend>
            <ul>
                <li v-for="(event, index) in diffusion.events" :key="index">
                    <span @click="changeSelectedEvent(index)" :class="eventActive.id == event.id ? 'active' : ''">{{ event.name }}</span>
                    <button type="button" :value="event.id" @click="removeEvent(event.id)" :title="messages.get('common', 'remove')">
                        <i class="fa fa-trash"></i>
                    </button>
                </li>
                <li v-if="diffusion.events.length === 0">
                    <p>{{ messages.get('dsi', 'event_form_triggers_empty') }}</p>
                </li>
            </ul>
            <button type="button" @click="fetchData(false)" :class="eventActive.id == 0 ? 'disabled' : ''"  :title="messages.get('dsi', 'add')">
                <i class="fa fa-plus"></i>
            </button>
        </fieldset>
        <div class="dsi-form-diffusion-event-exchange">
            <i class="fa fa-exchange" aria-hidden="true"></i>
        </div>
        <fieldset class="dsi-form-diffusion-event-add">
            <legend class="dsi-legend-setting">{{ messages.get('dsi', 'event_form_triggers_add') }}</legend>
            <formEvent 
                v-if="eventActive" 
                :event="eventActive" 
                :types="eventTypeList" 
                :is_model="false" 
                :item="diffusion.item" 
                :view="diffusion.view"
                :is_product="false"
                @addEvent="addEvent">
            </formEvent>
        </fieldset>
	</div>
</template>

<script>
    import formEvent from "../../triggers/components/formEvent.vue";
	export default {
		props : ["diffusion"],
        components: {
            formEvent,
        },
		data: function () {
			return {
			    eventTypeList: [],
			    eventActive: null
			}
		},
		created: function() {
			this.fetchData(true);
		},
		methods: {
			fetchData: async function(force = false) {
                if(force || this.eventActive.id != 0) {
                    const promises = [
                        this.ws.get('triggers', 'getTypeListAjax'),
                        this.ws.get('triggers', 'getEmptyInstance')
                    ];
                    const result = await Promise.all(promises);

                    this.eventTypeList = result[0];

                    this.$set(this, 'eventActive', {});
                    this.$set(this, 'eventActive', result[1]);
                }
			},
            addEvent: async function(event) {
                const index = this.diffusion.events.findIndex((e) => e.id == event.id);
                if(index == -1) {
                    this.diffusion.events.push(event);
                }

                let response = await this.ws.post('diffusions', 'save', this.diffusion);
				if (response.error) {
                    this.notif.error(this.messages.get('dsi', response.errorMessage));
				}else {
					this.notif.info(this.messages.get('common', 'success_save'));
				}
            },
            removeEvent: async function(idEvent) {
                let response = await this.ws.post('triggers', 'deleteEventDiffusion', {"num_event": idEvent, "num_diffusion": this.diffusion.id});
				if (response.error) {
					this.notif.error(this.messages.get('dsi', response.errorMessage));
				}else {
                    this.diffusion.events.splice(this.diffusion.events.findIndex((event) => event.id == idEvent), 1);
                    if(this.eventActive.id == idEvent) {
                        this.eventActive = await this.ws.get('triggers', 'getEmptyInstance');
                    }
                    this.notif.info(this.messages.get('common', 'success_save'));
				}
            },
            changeSelectedEvent: function(index) {
                this.eventActive = this.diffusion.events[index];
            }
		}
	}
</script>