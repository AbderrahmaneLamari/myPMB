<template>
	<div id="dsi-diffusion-accordion">
		<accordion :expanded="true" :title="messages.get('dsi', 'subscriber_accordion_subscriber_list')" index="1" key="1">
			<div id="dsi-subscribers-empr">
				<pagination-list :filter-fields="fields" :list="filteredList" format="table" :perPage="10" :startPage="1" :nbPage="6" :nbResultDisplay="true">
					<template #content="{ list }">
						<table class="uk-table uk-table-small uk-table-striped uk-table-middle">
							<thead>
								<tr>
									<th>{{ messages.get('dsi', 'subscriber_list_name') }}</th>
									<th>{{ messages.get('dsi', 'subscriber_list_id_empr') }}</th>
									<th>{{ messages.get('dsi', 'subscriber_list_cb') }}</th>
									<th>{{ messages.get('dsi', 'subscriber_list_email') }}</th>
									<th>{{ messages.get('dsi', 'subscriber_type') }}</th>
									<th v-if="idEntity">{{ messages.get('dsi', 'subscriber_list_actions') }}</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="(empr, subindex) in list" :key="subindex">
									<td>{{ empr.name }}</td>
									<td>{{ empr.settings.idEmpr }}</td>
									<td>{{ empr.settings.cb }}</td>
									<td>{{ empr.settings.email }}</td>
									<td>
										<i v-if="empr.type == 1" class="fa fa-hand-spock-o" :title="messages.get('dsi', 'subscriber_add_manually')"></i>
										<i v-if="empr.type == 2" class="fa fa-database" :title="messages.get('dsi', 'subscriber_from_bdd')"></i>
										<i v-if="empr.type == 3" class="fa fa-download" :title="messages.get('dsi', 'subscriber_imported')"></i>
									</td>
									<td class="dsi-table-right dsi-inline" v-if="idEntity">
										<button
											class="bouton" :title="messages.get('dsi', 'subscriber_unsubscribe')"
											@click.prevent="unsubscribeSubscriber(empr)" style="cursor:pointer;">
											<i class="fa fa-bell-slash-o" aria-hidden="true"></i>
										</button>
										<button v-if="channelType != undefined && (empr.type == 1 ||empr.type == 3)"
											class="bouton" :title="messages.get('dsi', 'subscriber_edit')"
											@click.prevent="$root.$emit('subscriberToEdit', empr)" style="cursor:pointer;">
											<i class="fa fa-edit" aria-hidden="true"></i>
										</button>
										<button v-if="empr.type == 1 ||empr.type == 3"
											class="bouton" :title="messages.get('dsi', 'subscriber_remove')"
											@click.prevent="removeSubscriber(empr)" style="cursor:pointer;">
											<i class="fa fa-times" aria-hidden="true"></i>
										</button>
									</td>
								</tr>
							</tbody>
						</table>
					</template>
				</pagination-list>
			</div>
		</accordion>
		<accordion v-if="$root.categ != 'subscriber_list' && subscriberList.id" :disabled="idEntity == 0" :title="messages.get('dsi', 'subscriber_accordion_manual_add')" index="2" key="2">
			<manual-add :subscribers="lists" :types="types" :id-entity="idEntity" :channel-type="channelType" @removeSubscriber="$emit('removeSubscriber', $event)" />
		</accordion>
		<accordion :expanded="$root.categ != 'subscriber_list'"  :disabled="idEntity == 0" :title="messages.get('dsi', 'subscriber_accordion_unsubscribers')" index="3" key="3">
			<div id="dsi-subscribers-empr">
				<pagination-list :filter-fields="fields" :list="unsubscribers" format="table" :perPage="10" :startPage="1" :nbPage="6" :nbResultDisplay="true">
					<template #content="{ list }">
						<table class="uk-table uk-table-small uk-table-striped uk-table-middle">
							<thead>
								<tr>
									<th>{{messages.get('dsi', 'subscriber_list_name')}}</th>
									<th>{{messages.get('dsi', 'subscriber_list_id_empr')}}</th>
									<th>{{messages.get('dsi', 'subscriber_list_cb')}}</th>
									<th>{{messages.get('dsi', 'subscriber_list_email')}}</th>
									<th>{{messages.get('dsi', 'subscriber_type')}}</th>
									<th>{{messages.get('dsi', 'subscriber_unsubscriber_type')}}</th>
									<th>{{messages.get('dsi', 'subscriber_list_actions')}}</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="(empr, subindex) in list" :key="subindex">
									<td>{{ empr.name }}</td>
									<td>{{ empr.settings.idEmpr }}</td>
									<td>{{ empr.settings.cb }}</td>
									<td>{{ empr.settings.email }}</td>
									<td>
										<i v-if="empr.type == 1" class="fa fa-hand-spock-o" :title="messages.get('dsi', 'subscriber_add_manually')"></i>
										<i v-if="empr.type == 2" class="fa fa-database" :title="messages.get('dsi', 'subscriber_from_bdd')"></i>
										<i v-if="empr.type == 3" class="fa fa-download" :title="messages.get('dsi', 'subscriber_imported')"></i>
									</td>
									<td>
										<span v-if="empr.updateType == 1">{{messages.get('dsi', 'subscriber_unsubscriber_type_no')}}</span>
										<span v-if="empr.updateType == 2">{{messages.get('dsi', 'subscriber_unsubscriber_type_yes')}}</span>
									</td>
									<td class="dsi-table-right dsi-inline">
										<button
											:disabled="empr.updateType == 2"
											class="bouton" :title="messages.get('dsi', 'subscriber_undo_unsubscribe')"
											@click.prevent="subscribe(empr)" style="cursor:pointer;">
											<i class="fa fa-bell-o" aria-hidden="true"></i>
										</button>
									</td>
								</tr>
							</tbody>
						</table>
					</template>
				</pagination-list>
			</div>
		</accordion>
	</div>
</template>

<script>
import accordion from "../../../../common/accordion/accordion.vue";
import manualAdd from "./manualAdd.vue";
import paginationList from "../../../components/paginationList.vue";

export default {
	props : ["subscriberList", "idEntity", "types", "channelType", "lists"],
	components : {
		accordion,
		manualAdd,
		paginationList
	},
	data : function() {
		return {
			fields : [
				{
					name : "name",
					label : "subscriber_list_name",
					type : "text"
				},
				{
					name : "email",
					label : "subscriber_list_email",
					type : "text"
				},
				{
					name : "idEmpr",
					label : "subscriber_list_id_empr",
					type : "number"
				},
				{
					name : "type",
					label : "subscriber_list_type",
					type : "text"
				}
			]
		}
	},
	computed : {
		filteredList : function() {
			if(! this.lists) {
				return this.subscriberList.subscribers;
			}
			if(this.subscriberList.subscribers.length || this.lists.subscribers.length) {
				return this.subscriberList.subscribers.concat(this.lists.subscribers).filter((s) => {
					if(this.unsubscribers.findIndex((u) => u.name == s.name) != -1) {
						return false;
					}
					return s.updateType == 0;
				});
			}
			return [];
		},
		unsubscribers : function() {
			if(! this.lists) {
				return [];
			}
			if(this.lists.subscribers) {
				return this.lists.subscribers.filter((s) => s.updateType > 0);
			}
			return [];
		}
	},
	methods : {
		removeSubscriber : async function(subscriber) {
			if (confirm(this.messages.get('dsi', 'confirm_del'))) {
				let response = await this.ws.post("subscribers", this.$root.categ + "/delete", subscriber);
				if (response.error) {
					this.notif.error(this.messages.get('dsi', response.errorMessage));
				} else {
					let i = this.lists.subscribers.findIndex((s) => s.id == subscriber.id);
					if(i != -1) {
						this.$delete(this.lists.subscribers, i);
					}
				}
			}
		},
		unsubscribeSubscriber : async function(subscriber) {
			let result = await this.ws.post("subscribers", this.$root.categ + "/unsubscribe/" + this.idEntity, subscriber);
			if(! result.error) {
				//On supprime de la subscriber list
				let i = this.subscriberList.subscribers.findIndex((s) => {
					if(s[this.Const.subscriberlist.subscriberDedoublonField] === undefined) {
						return s.settings[this.Const.subscriberlist.subscriberDedoublonField] == subscriber.settings[this.Const.subscriberlist.subscriberDedoublonField];
					}
					return s[this.Const.subscriberlist.subscriberDedoublonField] == subscriber[this.Const.subscriberlist.subscriberDedoublonField];
				});
				if(i != -1) {
					this.$delete(this.subscriberList.subscribers, i);
				}
				//On ajoute a la liste de l'entite
				this.$set(this.lists.subscribers, this.lists.subscribers.length, result);
			}
		},
		subscribe : async function(subscriber) {
			let result = await this.ws.post("subscribers", this.$root.categ + "/subscribe/" + this.idEntity, subscriber);
			if(! result.error) {
				//On supprime l'element de la liste de l'entite
				let i = this.lists.subscribers.findIndex((s) => s.id == subscriber.id);
				if(i != -1) {
					this.$delete(this.lists.subscribers, i);
				}
				//On ajoute l'element a la subscriber list
				this.$set(this.subscriberList.subscribers, this.subscriberList.subscribers.length, result);
			}
		}
	}
}
</script>