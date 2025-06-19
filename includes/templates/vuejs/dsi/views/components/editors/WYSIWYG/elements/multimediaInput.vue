<template>
    <div :id="block.id" class="wysiwyg-section" :style="block.style.block"  @click.prevent="$root.$emit('editBlock', block)">
        <div class="wysiwyg-section-label">
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
			<button type="button" :title="deleteMessage"
                @click.prevent="$root.$emit('removeBlock', { block : block, parent : parent })">
				<i class="fa fa-trash" aria-hidden="true"></i>
			</button>
		</div>
        <slot name="media"></slot>
    </div>
</template>

<script>
export default {
    props: ['block', 'parent', 'blockLabels'],
    created: function() {
        if (!this.block.id) {
            this.block.id = `${Date.now().toString(36)}${Math.random().toString(36).substring(2)}`;
        }

        if (!this.block.style.block) {
            this.$set(this.block.style, "block", {});
        }

        this.block.style.block["display"] = "flex";
        this.block.style.block["flex-grow"] = 1;
        this.block.style.block["flex"] = 1;
        // this.block.style["min-height"] = "32px";
        this.block.style["min-height"] = "min-content";

        if (! this.block.style.block["justify-content"]) {
            this.$set(this.block.style.block, "justify-content", "start");
        }
        if (! this.block.style.block["align-items"]) {
            this.$set(this.block.style.block, "align-items", "start");
        }

        if (this.block.style.block.textAlign) {
            this.block.style.block["justify-content"] = this.block.style.block.textAlign;
            delete this.block.style.block.textAlign;
        }
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
            return this.$parent.$vnode.key == 0 ? false : true;
        },
        showDownArrow: function() {
            return this.$parent.$parent.block && this.$parent.$vnode.key != (this.$parent.$parent.block.blocks.length-1) ? true : false;
        },
        upOrLeft: function() {
            return this.$parent.$parent.block.style.flexDirection == 'column' ? 'fa fa-arrow-up' : 'fa fa-arrow-left'
        },
        downOrRight: function() {
            return this.$parent.$parent.block.style.flexDirection == 'column' ? 'fa fa-arrow-down' : 'fa fa-arrow-right'
        }
    },
    methods: {
        changeImage(event) {
            let files = event.target.files || event.dataTransfer.files;
            if (!files.length) {
                return;
            }
            this.createImage(files[0]);
        },
        createImage(file) {
            let image = new Image();
            let reader = new FileReader();

            reader.onload = (e) => {
                image = e.target.result;
                this.$set(this.block, "content", image);
            };
            reader.readAsDataURL(file);
        }
    }
}
</script>