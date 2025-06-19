<template>
    <div v-if="!view.id">
        <span>{{ messages.get('dsi', 'view_wysiwyg_view_unable_to_save') }}</span>
    </div>
    <div v-else :id="block.id" class="wysiwyg-section" :style="block.style">
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
            <button v-if="!this.root" type="button" :title="deleteMessage"
                @click.prevent="$root.$emit('removeBlock', { block: block, parent: parent })">
                <i class="fa fa-trash" aria-hidden="true"></i>
            </button>
        </div>
        <div v-if="childView && childView.settings">
            <lockable :locked="childView.settings.locked">
                <div v-if="childView && childView.settings && childView.settings.layer">
                    <block v-for="(block, index) in childView.settings.layer.blocks" :key="index" :block="block"
                        :root="true" :blockTypes="blockTypes" :blockLabels="blockLabels" :view="view"
                        :parent="childView"></block>
                </div>
                <div v-else>
                    <i class="fa fa-clipboard" aria-hidden="true"></i>
                </div>
            </lockable>
        </div>
    </div>
</template>

<script>
import lockable from '../../../../../components/lockable.vue';
export default {
    name: 'viewImportInput',
    props: ['block', 'blockTypes', 'blockLabels', 'view', "root", "parent"],
    components: {
        //VUEJS fait chier quand on utilise un composant parent en sous composant
        //Donc on force l'import
        block: () => import('./block.vue'),
        lockable
    },
    created: function () {
        if (!this.block.id) {
            this.block.id = `${Date.now().toString(36)}${Math.random().toString(36).substring(2)}`;
        }
        if (!this.block.style) {
            this.$set(this.block, "style", {});
        }
        if (!this.block.content) {
            this.$set(this.block, "content", {});
        }
        if (!this.block.content.viewId) {
            this.$set(this.block.content, "viewId", 0);
        }
        this.block.style["display"] = "flex";

        if (!this.block.style.flexDirection) {
            this.$set(this.block.style, "flexDirection", "column")
        }
        this.block.style["flex-grow"] = 1;
        this.block.style["flex"] = 1;
        // this.block.style["min-height"] = "32px";
        this.block.style["min-height"] = "min-content";
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
        },
        childView: function () {
            if (this.block.content.viewId == 0) {
                return {}
            }
            let view = this.view.childs.find((v) => v.id == this.block.content.viewId);
            return typeof view === 'undefined' ? {} : view;
        }
    }
}
</script>
<style scoped>
::v-deep .wysiwyg-section:hover {
    border: 1px solid #ff9ed0 !important;
    padding-top: 25px;
}

::v-deep .wysiwyg-section-selected {
    border: 1mm ridge #ff9ed0 !important;
}
</style>