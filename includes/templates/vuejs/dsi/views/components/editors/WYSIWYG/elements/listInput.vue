<template>
    <!--
    <div :id="block.id" class="wysiwyg-section" :style="block.style" @mouseenter.stop="hoverBlock" @mouseleave.stop="hoverBlock">
-->
    <div :id="block.id" class="wysiwyg-section" :style="block.style" @click.prevent="$root.$emit('editBlock', block)">
        <div class="wysiwyg-section-label">
            <span>{{ blockLabels[block.type] }}</span>
        </div>
        <div v-if="block" class="wysiwyg-add-section-actions">
            <button v-if="showUpArrow" type="button" :title="upOrLeftMessage"
                @click.prevent="$root.$emit('moveBlock', { block: block, direction: 'up', parent: parent })">
                <i :class="upOrLeft" aria-hidden="true"></i>
            </button>
            <button v-if="showDownArrow" type="button" :title="downOrRightMessage"
                @click.prevent="$root.$emit('moveBlock', { block: block, direction: 'down', parent: parent })">
                <i :class="downOrRight" aria-hidden="true"></i>
            </button>
            <button type="button" :title="editMessage" @click.prevent="$root.$emit('editBlock', block)">
                <i class="fa fa-pencil" aria-hidden="true"></i>
            </button>
            <button type="button" :title="deleteMessage"
                @click.prevent="$root.$emit('removeBlock', { block: block, parent: parent })">
                <i class="fa fa-trash" aria-hidden="true"></i>
            </button>
        </div>
        <ul>
            <li :style="block.list.style" v-for="(element, index) in block.list.elements" :key="index">{{ element }}
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    name: "listInput",
    props: ['block', 'blockTypes', 'blockLabels', 'parent'],
    created: function () {
        if (!this.block.id) {
            this.block.id = `${Date.now().toString(36)}${Math.random().toString(36).substring(2)}`;
        }

        this.block.style = {
            display: "flex",
            flexDirection: this.block.style.flexDirection || "column",
            flexGrow: 1,
            flex: 1,
            minHeight: "32px"
        };

        if (!this.block.list) {
            this.$set(this.block, "list", {});
            this.$set(this.block.list, "style", { lineHeight: "10px", fontSize: "18px" });
            this.$set(this.block.list, "elements", ["Item 1", "Item 2", "Item 3"]);
        }
    },
    computed: {
        downOrRightMessage: function () {
            let message = "";
            if (this.$parent?.block?.style?.flexDirection == 'column') {
                message = this.messages.get('dsi', 'view_wysiwyg_move_down');
            } else {
                message = this.messages.get('dsi', 'view_wysiwyg_move_right');
            }
            return message.replace('%s', this.blockLabels[this.block.type].toLowerCase());
        },
        upOrLeftMessage: function () {
            let message = "";
            if (this.$parent?.block?.style?.flexDirection == 'column') {
                message = this.messages.get('dsi', 'view_wysiwyg_move_up');
            } else {
                message = this.messages.get('dsi', 'view_wysiwyg_move_left');
            }
            return message.replace('%s', this.blockLabels[this.block.type].toLowerCase());
        },
        editMessage: function () {
            return this.messages.get('dsi', 'view_wysiwyg_edit_element')
                .replace('%s', this.blockLabels[this.block.type].toLowerCase())
        },
        deleteMessage: function () {
            return this.messages.get('dsi', 'view_wysiwyg_delete_element')
                .replace('%s', this.blockLabels[this.block.type].toLowerCase())
        },
        showUpArrow: function () {
            return this.$vnode.key == 0 ? false : true;
        },
        showDownArrow: function () {
            return this.$parent.block && this.$vnode.key != (this.$parent.block.blocks.length - 1) ? true : false;
        },
        upOrLeft: function () {
            return this.$parent.block.style.flexDirection == 'column' ? 'fa fa-arrow-up' : 'fa fa-arrow-left'
        },
        downOrRight: function () {
            return this.$parent.block.style.flexDirection == 'column' ? 'fa fa-arrow-down' : 'fa fa-arrow-right'
        }
    },
    /*    methods: {
            hoverBlock: function(event) {
                if(event.target.classList.contains("wysiwyg-section")) {
                    if(event.type == "mouseenter") {
                        event.target.firstElementChild.style.display = "block";
                    }else {
                        event.target.firstElementChild.style.display = "none";
                    }
                }
            },
        }*/
}
</script>