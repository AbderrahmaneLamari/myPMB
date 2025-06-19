<template>
	<div :id="this.block.id" class="wysiwyg-section" :style="style" @click.self="$root.$emit('editBlock', block)">
        <div class="wysiwyg-section-label" @click.prevent="$root.$emit('editBlock', block)">
            <span>{{ blockLabels[block.type] }}</span>
        </div>
		<div v-if="block" class="wysiwyg-add-section-actions">
			<button v-if="showUpArrow" type="button" :title="upOrLeftMessage"
				@click.prevent="$root.$emit('moveBlock', { block: block, direction: 'up', parent : parent })">
				<i :class="upOrLeft" aria-hidden="true"></i>
			</button>
			<button v-if="showDownArrow" type="button" :title="downOrRightMessage"
				@click.prevent="$root.$emit('moveBlock', { block: block, direction: 'down', parent : parent })">
				<i :class="downOrRight" aria-hidden="true"></i>
			</button>
			<button type="button" :title="editMessage" @click.prevent="$root.$emit('editBlock', block)">
				<i class="fa fa-pencil" aria-hidden="true"></i>
			</button>
			<button v-if="isDuplicating" @click.prevent="duplicateBlock"
				:title="messages.get('dsi', 'view_wysiwyg_paste_element')">
				<i class="fa fa-clipboard" aria-hidden="true"></i>
			</button>
			<button v-else @click.prevent="initDuplication(block)"
				:title="messages.get('dsi', 'view_wysiwyg_copy_element')">
				<i class="fa fa-clone" aria-hidden="true"></i>
			</button>
			<button v-if="!this.root" type="button" :title="deleteMessage"
				@click.prevent="$root.$emit('removeBlock', { block : block, parent : parent })">
				<i class="fa fa-trash" aria-hidden="true"></i>
			</button>
			<button type="button" @click.prevent="show = true"
				:title="messages.get('dsi', 'view_wysiwyg_add_element')">
				<i class="fa fa-plus" aria-hidden="true"></i>
			</button>
		</div>
		<component v-for="(element, index) in block.blocks"
                   :key="index"
                   :is="blockTypes[element.type]"
                   :view="view" :block="element"
                   :blockTypes="blockTypes"
                   :blockLabels="blockLabels"
                   :root="false"
                   :parent="parent">
        </component>
		<addBlock :view="view" :blocks="block.blocks" :show="show" @close="show = false" :root="false"></addBlock>
	</div>
</template>

<script>
	import addBlock from "../addBlock.vue";
	import textInput from "./textInput.vue";
	import imageInput from "./imageInput.vue";
	import videoInput from "./videoInput.vue";
	import listInput from "./listInput.vue";
	import textEditorInput from "./textEditorInput.vue";
	import viewInput from "./viewInput.vue";
	import viewImportInput from "./viewImportInput.vue";

	export default {
        name: "block",
		props: ["block", "blockTypes", "blockLabels", "root", "view", "parent"],
		components: {
			addBlock,
			textInput,
			imageInput,
			videoInput,
			listInput,
			textEditorInput,
			viewInput,
			viewImportInput,
        },
		data: function () {
			return {
				show: false,
				STYLE_CONST: {
					HORIZONTAL: "row",
					VERTICAL: "column"
				},
				isDuplicating : false
			}
		},
		created: function() {
			if(!this.block.id) {
				this.block.id = Date.now().toString(36) + Math.random().toString(36).substring(2);
			}

			this.block.style["display"] = "flex";

			if(!this.block.style.flexDirection) {
				if (this.root) {
					this.$set(this.block.style, "flexDirection", this.STYLE_CONST.VERTICAL);
				} else {
					this.$set(this.block.style, "flexDirection", this.STYLE_CONST.HORIZONTAL);
				}
			}

			if(!this.block.widthEnabled) {
				this.block.style["flex"] = 1;
				this.block.style["flex-grow"] = 1;
			}
			// this.block.style["min-height"] = "32px";
			this.block.style["min-height"] = "min-content";

			this.initListners();
		},
		computed: {
			downOrRightMessage: function() {
				let message = "";
				if (this.$parent?.block?.style?.flexDirection == 'column') {
					message = this.messages.get('dsi', 'view_wysiwyg_move_down');
				} else {
					message = this.messages.get('dsi', 'view_wysiwyg_move_right');
				}
				return message.replace('%s', this.blockLabels[this.block.type].toLowerCase());
			},
			upOrLeftMessage: function() {
				let message = "";
				if (this.$parent?.block?.style?.flexDirection == 'column') {
					message = this.messages.get('dsi', 'view_wysiwyg_move_up');
				} else {
					message = this.messages.get('dsi', 'view_wysiwyg_move_left');
				}
				return message.replace('%s', this.blockLabels[this.block.type].toLowerCase());
			},
			editMessage: function() {
				return this.messages.get('dsi', 'view_wysiwyg_edit_element')
					.replace('%s', this.blockLabels[this.block.type].toLowerCase())
			},
			deleteMessage: function() {
				return this.messages.get('dsi', 'view_wysiwyg_delete_element')
					.replace('%s', this.blockLabels[this.block.type].toLowerCase())
			},
			showUpArrow: function() {
				return this.$vnode.key == 0 ? false : true;
			},
			showDownArrow: function() {
				return this.$parent.block && this.$vnode.key != (this.$parent.block.blocks.length-1) ? true : false;
			},
			upOrLeft: function() {
				return this.$parent.block && this.$parent.block.style.flexDirection == 'column' ? 'fa fa-arrow-up' : 'fa fa-arrow-left'
			},
			downOrRight: function() {
				return this.$parent.block && this.$parent.block.style.flexDirection == 'column' ? 'fa fa-arrow-down' : 'fa fa-arrow-right'
			},
			style: function() {
				let style = this.helper.cloneObject(this.block.style);
				if (style['padding-top']) {
					style['padding-top'] = `CALC(25px + ${style['padding-top']})`;
				}
				return style;
			}
		},
		methods: {
			initDuplication : function(block) {
				//On ajoute le block dans la session
				sessionStorage.setItem("duplicateBlock", JSON.stringify(block));
				//On envoie un event pour indiquer une duplication en cours
				let event = new CustomEvent("duplicateBlock", { bubbles : true });
				window.dispatchEvent(event);
			},
			duplicateBlock : function() {
				//On recupere le block et on lui donne un nouvel id avant de l'ajouter aux blocks
				let block = JSON.parse(sessionStorage.getItem("duplicateBlock"));
				this.changeBlockId(block);

				this.$set(this.block.blocks, this.block.blocks.length, block);

				//On supprime le block de la session
				sessionStorage.removeItem('duplicateBlock');

				//On envoie un event pour indiquer la fin de la duplication
				let event = new CustomEvent("endDuplicateBlock", { bubbles : true });
				window.dispatchEvent(event);
			},
			initListners : function() {
				addEventListener("endDuplicateBlock", e => this.isDuplicating = false);
				addEventListener("duplicateBlock", e =>	this.isDuplicating = true);
			},
			changeBlockId : function(block) {
				block.id = Date.now().toString(36) + Math.random().toString(36).substring(2);
				for(let i in block.blocks) {
					this.changeBlockId(block.blocks[i]);
				}
				return block;
			}
		}
	}
</script>