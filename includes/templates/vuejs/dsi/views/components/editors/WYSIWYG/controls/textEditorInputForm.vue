<template>
    <div>
        <div class="dsi-form-group dsi-form-wysiwyg">
            <label class="etiquette" for="wysiwyg-patterns">{{ messages.get('dsi', 'label_pattern') }}</label>
            <div class="dsi-form-group-content">
                <div class="dsi-form-group-line">
                    <select name="wysiwyg-patterns" id="wysiwyg-patterns" class="dsi-patterns" v-model="pattern">
                        <option value="">{{ messages.get('common', 'common_default_select') }}</option>
                        <optgroup :label="group" v-for="(patterns, group) in filteredPatterns" :key="group">
                            <option v-for="(label, pattern) in patterns" :value="pattern" :key="pattern">
                                {{ label }}
                            </option>
                        </optgroup>
                    </select>
                    <input type="button" class="bouton" :value="messages.get('common', 'common_insert_pattern')" @click="addPattern"/>
                </div>
            </div>
        </div>
        <div class="dsi-form-group dsi-form-wysiwyg" v-if="patterns.dynamicGroups && patterns.dynamicGroups[pattern]">
            <label class="etiquette" for="wysiwyg-patterns">{{ patterns.dynamicGroups[pattern].label }}</label>
            <select v-if="patterns.dynamicGroups[pattern].type == 'select'" v-model="dynamicValue">
                <option value="" disabled>{{ patterns.dynamicGroups[pattern].defaultOption }}</option>
                <option v-for="option in patterns.dynamicGroups[pattern].options" :key="option.value" :value="option.value">{{option.label}}</option>
            </select>
            <input v-else :type="patterns.dynamicGroups[pattern].type" v-model="dynamicValue" />
        </div>
        <div class="dsi-wysiwyg-text-editor">
            <editor v-model="block.content" :init="configEditor"></editor>
        </div>
    </div>
</template>

<script>
    import tinymce from 'tinymce';

    import 'tinymce/icons/default';
    import 'tinymce/themes/silver';
    import 'tinymce/skins/ui/oxide/skin.css';
    import 'tinymce/models/dom';

    import 'tinymce/plugins/advlist';
    import 'tinymce/plugins/code';
    import 'tinymce/plugins/link';
    import 'tinymce/plugins/lists';
    import 'tinymce/plugins/table';
    import 'tinymce/plugins/insertdatetime';
    import 'tinymce/plugins/image';
    import 'tinymce/plugins/media';
    import 'tinymce/plugins/fullscreen';
    import 'tinymce/plugins/link';
    import 'tinymce/plugins/nonbreaking';
    import 'tinymce/plugins/preview';
    import 'tinymce/plugins/searchreplace';
    import 'tinymce/plugins/directionality';
    import 'tinymce/plugins/visualchars';
    import 'tinymce/plugins/anchor';
    import 'tinymce/plugins/charmap';

    import editor from "@tinymce/tinymce-vue";

	export default {
        name: "textEditorInputForm",
		props: ["block"],
        components: {
            editor
        },
        data: function() {
            return {
                pattern: "",
                patterns: {},
                dynamicValue : "",
                configEditor: {
                    height: 500,
                    setup: (editor) => {
                        editor.on('init', () => {
                            this.hiddenLoader();
                        });
                        editor.on('FullscreenStateChanged', (event) => {
                            this.hiddenNode('menu', event.state);
                            this.hiddenNode('notification', event.state);
                        });
                    },
                    entity_encoding : 'raw',
                    convert_urls : false,
                    language: 'fr_FR',
                    language_url : './javascript/tinymce/fr_FR.js',
                    toolbar: ['undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify outdent indent | bullist numlist | forecolor backcolor | fontsizeselect | fontselect',
                            'link image | anchor | fullscreen preview code'],
                    plugins: "table insertdatetime image media link nonbreaking preview searchreplace directionality visualchars code anchor fullscreen charmap lists advlist",
                    browser_spellcheck : false,
                    removed_menuitems: 'newdocument',
                    skin: false,
                    content_css: false,
                    formats: {
                        p: { block: 'p', styles: { 'margin': '0' }, exact: true },
                    },
                    promotion: false,
                    fullscreen_native: true
                }
            }
        },
        created: function() {
            this.fetchPatterns();
        },
        mounted: function() {
            this.showLoader();
        },
        computed : {
            filteredPatterns: function() {
                if(! Object.keys(this.patterns).length) {
                    return [];
                }
                let result = {};
                for(let pattern in this.patterns) {
                    if(pattern != this.Const.views.dynamicGroupsKey) {
                        result[pattern] = this.patterns[pattern];
                    }
                }
                return result;
            }
        },
        methods: {
            hiddenNode: function(nodeId, state) {
                const node = document.getElementById(nodeId);
                if (node) {
                    if (state) {
                        node.style.display = 'none';
                    } else {
                        node.style.display = 'unset';
                    }
                }
            },
            fetchPatterns: async function() {
                let result = await this.ws.get('input', 'patterns');
                if (result.error) {
                    this.notif.error(result.errorMessage);
                } else {
                    this.$set(this, 'patterns', result);
                }
            },
            addPattern: function() {
                if(this.patterns["dynamicGroups"][this.pattern] && this.dynamicValue) {
                    this.block.content += this.pattern.substring(0, this.pattern.length-2) + `_${this.dynamicValue}!!`;

                } else {
                    this.block.content += this.pattern;
                }
                this.pattern = "";
            }
        }
	}
</script>