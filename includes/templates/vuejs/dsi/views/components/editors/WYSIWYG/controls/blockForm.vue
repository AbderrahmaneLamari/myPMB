<template>
    <div>
        <div>
            <h3>{{ messages.get('dsi', 'view-wysiwyg-placement') }}</h3>
            <div class="dsi-form-group dsi-form-wysiwyg">
                <label class="etiquette" for="wysiwyg-placement-direction">
                    {{ messages.get('dsi', 'view-wysiwyg-placement-direction') }}
                </label>
                <div class="dsi-form-group-content">
                    <div class="dsi-form-group-line">
                        <select id="wysiwyg-placement-direction"
                            name="wysiwyg-placement-direction"
                            v-model="block.style.flexDirection">
                            <option value="row">{{ messages.get('dsi', 'view-wysiwyg-placement-row') }}</option>
                            <option value="column">{{ messages.get('dsi', 'view-wysiwyg-placement-column') }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="dsi-form-group dsi-form-wysiwyg">
                <label class="etiquette" for="wysiwyg-placement-width-enabled">
                    {{ messages.get('dsi', 'view_wysiwyg_placement_width_enabled') }}
                </label>
                <div class="dsi-form-group-content">
                    <input 
                        type="checkbox" 
                        id="wysiwyg-placement-width-enabled" 
                        name="wysiwyg-placement-width-enabled"
                        v-model="block.widthEnabled" 
                        @change="resetWidth">
                </div>
            </div>
            <div class="dsi-form-group dsi-form-wysiwyg dsi-form-wysiwyg-width" v-if="block.widthEnabled">
                <label class="etiquette" for="wysiwyg-input-width">
                    {{ messages.get('dsi', 'view_wysiwyg_placement_width') }}
                </label>
                <div class="dsi-form-group-content">
                    <input 
                        type="number" 
                        min="0" 
                        id="wysiwyg-input-width" 
                        name="wysiwyg-input-width" 
                        :value="getWidth()"
                        class="wysiwyg-width-input"
                        @input="changeWidth($event)">
                    <select v-model="widthUnit" @change="changeWidth($event, true)">
                        <option v-for="unit of arrayUnit" :value="unit">{{ unit }}</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="wysiwyg-block-conditions" v-if="$root.diffusion">
            <h3>{{ messages.get('dsi', 'view-wysiwyg-condition-display') }}</h3>
            <conditions :settings="block" :context="'wysiwyg'"></conditions>
        </div>
        <div>
            <h3>{{ messages.get('dsi', 'view-wysiwyg-bg') }}</h3>
            <div class="dsi-form-group dsi-form-wysiwyg">
                <label class="etiquette" for="wysiwyg-bg-color">
                    {{ messages.get('dsi', 'view-wysiwyg-bg-color') }}
                </label>
                <div class="dsi-form-group-content">
                    <input type="color"
                        id="wysiwyg-bg-color"
                        name="wysiwyg-bg-color"
                        ref="wysiwyg_bg_color"
                        v-model="bgColor"
                        @change="convertHexToRGBA($event)">
                    <button class="color-reset"
                        v-if="block.style.backgroundColor"
                        @click="block.style.backgroundColor = ''; bgColor = '#ffffff'; bgOpacity = 1"
                        type="button">
                        <i class="fa fa-times" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
            <div class="dsi-form-group dsi-form-wysiwyg">
                <label class="etiquette" for="wysiwyg-bg-opacity">
                    {{ messages.get('dsi', 'view_wysiwyg_bg_opacity') }}
                </label>
                <div class="dsi-form-group-content">
                    <input type="range"
                        id="wysiwyg-bg-opacity"
                        name="wysiwyg-bg-opacity"
                        ref="wysiwyg_bg_opacity"
                        max="1"
                        step="0.01"
                        v-model="bgOpacity"
                        @input="convertHexToRGBA($event, true)">
                </div>
            </div>    
            <hr>
            <div class="dsi-form-group dsi-form-wysiwyg">
                <label class="etiquette" for="wysiwyg-bg-image">{{ messages.get('dsi', 'view-wysiwyg-bg-img') }}</label>
                <div class="dsi-form-group-content">
                    <input v-if="!block.style.backgroundImage"
                        type="file" id="wysiwyg-bg-image"
                        name="wysiwyg-bg-image" @change="changeBgImage">
                    <div v-else class="wysiwyg-bg-image-preview">
                        <img width="48" height="48" :src="dataURLToData(block.style.backgroundImage)" alt="">
                        <button class="bg-reset"
                            v-if="block.style.backgroundImage"
                            @click="block.style.backgroundImage = ''"
                            type="button">
                            <i class="fa fa-times" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="dsi-form-group dsi-form-wysiwyg">
                <label class="etiquette" for="wysiwyg-bg-img-repeat">
                    {{ messages.get('dsi', 'view-wysiwyg-bg-img-repeat') }}
                </label>
                <div class="dsi-form-group-content">
                    <select v-model="block.style.backgroundRepeat" id="wysiwyg-bg-img-repeat">
                        <option value="no-repeat">
                            {{ messages.get('dsi', 'view-wysiwyg-bg-img-repeat-norepeat') }}
                        </option>
                        <option value="repeat">{{ messages.get('dsi', 'view-wysiwyg-bg-img-repeat-repeat') }}</option>
                    </select>
                </div>
            </div>
            <div class="dsi-form-group dsi-form-wysiwyg">
                <label class="etiquette" for="wysiwyg-bg-img-size">
                    {{ messages.get('dsi', 'view-wysiwyg-bg-img-size') }}
                </label>
                <div class="dsi-form-group-content">
                    <select v-model="bgSize" id="wysiwyg-bg-img-size">
                        <option value="auto">{{ messages.get('dsi', 'view-wysiwyg-bg-img-size-auto') }}</option>
                        <option value="cover">{{ messages.get('dsi', 'view-wysiwyg-bg-img-size-cover') }}</option>
                        <option value="contain">{{ messages.get('dsi', 'view-wysiwyg-bg-img-size-contain') }}</option>
                        <option value="custom">{{ messages.get('dsi', 'view-wysiwyg-bg-img-size-custom') }}</option>
                    </select>
                    <input type="hidden" v-model="block.style.backgroundSize">
                    <div v-show="bgSize === 'custom'">
                        <input type="number" id="wysiwyg-bg-width" name="wysiwyg-bg-width" @input="changeBgSize">
                        <select v-model="bgSizeUnit" @change="changeBgSize($event, true)">
                            <option v-for="(unit, index) of arrayUnit" :value="unit" :key="index">{{ unit }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="dsi-form-wysiwyg-mg">
            <h3>{{ messages.get('dsi', 'view-wysiwyg-mg') }}</h3>
            <div v-for="direction of arrayDirections" class="dsi-form-group dsi-form-wysiwyg">
                <label class="etiquette" :for="'wysiwyg-mg-' + direction">
                    {{ messages.get('dsi', 'view-wysiwyg-mg-' + direction) }}
                </label>
                <div class="dsi-form-group-content">
                    <input min="0" type="number" :id="'wysiwyg-mg-' + direction" :name="'wysiwyg-mg-' + direction"
                        class="wysiwyg-mg-input" :value="getMargin(direction)" @input="changeMargin($event, direction)">

                    <select v-model="marginsUnit[direction]" @change="changeMargin($event, direction, true)">
                        <option v-for="unit of arrayUnit" :value="unit">{{ unit }}</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="dsi-form-wysiwyg-pd">
            <h3>{{ messages.get('dsi', 'view-wysiwyg-pd') }}</h3>
            <div v-for="direction of arrayDirections" class="dsi-form-group dsi-form-wysiwyg">
                <label class="etiquette" :for="'wysiwyg-pd-' + direction">
                    {{ messages.get('dsi', 'view-wysiwyg-mg-' + direction) }}
                </label>
                <div class="dsi-form-group-content">
                    <input min="0" type="number" :id="'wysiwyg-pd-' + direction" :name="'wysiwyg-pd-' + direction"
                        class="wysiwyg-pd-input" :value="getPadding(direction)"
                        @input="changePadding($event, direction)">

                    <select v-model="paddingsUnit[direction]" @change="changePadding($event, direction, true)">
                        <option v-for="unit of arrayUnit" :value="unit">{{ unit }}</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import conditions from '@dsi/components/Conditions/conditions.vue';

export default {
    name: "blockForm",
    props: ["block", "view", "item"],
    components: {
        conditions
    },
    data: function () {
        return {
            marginsUnit: { right: "px", left: "px", top: "px", bottom: "px" },
            paddingsUnit: { right: "px", left: "px", top: "px", bottom: "px" },
            arrayUnit: ["px", "rem", "%", "vh", "vw"],
            arrayDirections: ["right", "left", "top", "bottom"],
            bgSize: "auto",
            bgSizeUnit: "px",
            bgSizeList: ["auto", "cover", "contain"],
            // heightUnit: "px",
            widthUnit: "px",
            blockLabels: {
                1: this.messages.get('dsi', 'view_wysiwyg_block'),
                2: this.messages.get('dsi', 'view_wysiwyg_input_text'),
                3: this.messages.get('dsi', 'view_wysiwyg_input_image'),
                4: this.messages.get('dsi', 'view_wysiwyg_input_video'),
                5: this.messages.get('dsi', 'view_wysiwyg_input_list'),
                6: this.messages.get('dsi', 'view_wysiwyg_input_text_rich'),
                7: this.messages.get('dsi', 'view_wysiwyg_views'),
                8: this.messages.get('dsi', 'view_wysiwyg_view_wysiwyg')
            },
            condition: "",
            bgColor: "",
            bgOpacity: 1

        }
    },
    updated: function() {
        if (typeof domUpdated	=== "function") {
            domUpdated();
        }
    },
    mounted: function() {
        if (typeof domUpdated	=== "function") {
            domUpdated();
        }
    },
    watch: {
        bgSize: function (newVal) {
            if (this.bgSize === "custom") {
                if (!this.block.style.backgroundSize) {
                    this.$set(this.block.style, "backgroundSize", "");
                }

                const formSize = document.getElementById("wysiwyg-bg-width");
                if (formSize && formSize.value != "") {
                    this.$set(this.block.style, "backgroundSize", formSize.value + this.bgSizeUnit);
                }
                return;
            }
            this.$set(this.block.style, "backgroundSize", newVal);
        }
    },
    created: function () {
        if (!this.block.style.flexDirection) {
            this.$set(this.block.style, "flexDirection", "column");
        }

        if (!this.block.widthEnabled) {
            this.$set(this.block, "widthEnabled", false);
        }

        if (!this.block.style.backgroundRepeat) {
            this.$set(this.block.style, "backgroundRepeat", "no-repeat");
        }

        if (this.block.style.backgroundImage) {
            if (document.getElementById("wysiwyg-bg-image")) {
                let file = this.dataURLtoFile(this.block.style.backgroundImage);
                let container = new DataTransfer();
                container.items.add(file);
                document.getElementById("wysiwyg-bg-image").files = container.files
            }
        }

        if (this.block.style.backgroundSize) {
            this.bgSize = "custom"
            for (const size of this.bgSizeList) {
                if (this.block.style.backgroundSize == size) {
                    this.bgSize = size;
                    break;
                }
            }

            if (this.bgSize == "custom") {
                for (const unit of this.arrayUnit) {
                    if (this.block.style.backgroundSize.includes(unit)) {
                        this.bgSizeUnit = unit;
                        let node = document.getElementById("wysiwyg-bg-width");
                        if (node) {
                            node.value = this.block.style.backgroundSize.replace(unit, "");
                        }
                        break;
                    }
                }
            }
        }

        if(this.block.style.backgroundColor) {
            this.bgColor = this.convertRGBToHex(this.block.style.backgroundColor);
            this.bgOpacity = this.RGBAToArray(this.block.style.backgroundColor)[3];
        }

        if (this.block.style.width) {
                for (const unit of this.arrayUnit) {
                    if (this.block.style.width.includes(unit)) {
                        this.widthUnit = unit;
                        let node = document.getElementById("wysiwyg-input-width");
                        if (node) {
                            node.value = this.block.style.width.replace(unit, "");
                        }
                        break;
                    }
                }
            }


        for (const direction of this.arrayDirections) {
            if (this.block.style["margin-" + direction]) {
                for (const unit of this.arrayUnit) {
                    if (this.block.style["margin-" + direction].includes(unit)) {
                        this.marginsUnit[direction] = unit;
                        let node = document.getElementById("wysiwyg-mg-" + direction);
                        if (node) {
                            node.value = this.block.style["margin-" + direction].replace(unit, "");
                        }
                        break;
                    }
                }
            }

            if (this.block.style["padding-" + direction]) {
                for (const unit of this.arrayUnit) {
                    if (this.block.style["padding-" + direction].includes(unit)) {
                        this.paddingsUnit[direction] = unit;
                        let node = document.getElementById("wysiwyg-pd-" + direction);
                        if (node) {
                            node.value = this.block.style["padding-" + direction].replace(unit, "");
                        }
                        break;
                    }
                }
            }
        }
    },
    methods: {
        getWidth: function () {
            this.block.style["flex"] = "none";

            if (this.block.style["width"]) {
                return parseInt(this.block.style["width"], 10);
            }
            return 800;
        },
        changeWidth: function (event, reload = false) {
            this.block.style["flex"] = "none";

            if (reload) {
                this.$set(this.block.style, "width", event.target.previousElementSibling.value + this.widthUnit);
                return;
            }
            this.$set(this.block.style, "width", event.target.value + this.widthUnit);
        },
        getMargin: function (direction) {
            if (this.block.style["margin-" + direction]) {
                return parseInt(this.block.style["margin-" + direction], 10);
            }
            return 0;
        },
        changeMargin: function (event, direction, reload = false) {
            if (reload) {
                this.$set(this.block.style, "margin-" + direction, event.target.previousElementSibling.value + this.marginsUnit[direction]);
                return;
            }
            this.$set(this.block.style, "margin-" + direction, event.target.value + this.marginsUnit[direction]);
        },
        getPadding: function (direction) {
            if (this.block.style["padding-" + direction]) {
                return parseInt(this.block.style["padding-" + direction], 10);
            }
            return 0;
        },
        changePadding: function (event, direction, reload = false) {
            if (reload) {
                this.$set(this.block.style, "padding-" + direction, event.target.previousElementSibling.value + this.paddingsUnit[direction]);
                return;
            }
            this.$set(this.block.style, "padding-" + direction, event.target.value + this.paddingsUnit[direction]);
        },
        changeBgSize: function (event, reload = false) {
            if (reload) {
                const node = document.getElementById('wysiwyg-bg-width');
                this.$set(this.block.style, "backgroundSize", (node?.value ?? 0) + this.bgSizeUnit);
                return;
            }
            this.$set(this.block.style, "backgroundSize", event.target.value + this.bgSizeUnit);
        },
        changeBgImage(event) {
            let files = event.target.files || event.dataTransfer.files;
            if (!files.length) return;
            this.createImage(files[0]);
        },
        createImage(file) {
            let image = new Image();
            let reader = new FileReader();

            reader.onload = (e) => {
                image = e.target.result;
                this.$set(this.block.style, "backgroundImage", "url(" + image + ")");
            };
            reader.readAsDataURL(file);
        },
        dataURLToData: function (dataURL) {
            return dataURL.match(/\((.*?)\)/)[1].replace(/('|")/g, '');
        },
        dataURLtoFile: function (dataURL) {
            let url = this.dataURLToData(dataURL);

            let arr = url.split(','),
                mime = arr[0].match(/:(.*?);/)[1],
                bstr = atob(arr[1]),
                n = bstr.length,
                u8arr = new Uint8Array(n);

            while (n--) {
                u8arr[n] = bstr.charCodeAt(n);
            }

            return new File([u8arr], "myfile", { type: mime });
        },
        convertHexToRGBA: function(e, isOpacity = false) {
            const opacity = !isOpacity ? this.$refs.wysiwyg_bg_opacity.value : e.target.value;
            const color = !isOpacity ? e.target.value : this.$refs.wysiwyg_bg_color.value;
            const colorHex = `rgba(${parseInt(color.slice(-6, -4), 16)}, ${parseInt(color.slice(-4, -2), 16)}, ${parseInt(color.slice(-2), 16)}, ${opacity})`;
            
            this.$set(this.block.style, "backgroundColor", colorHex);
        },
        convertRGBToHex: function(rgbString) {
            let rgbArray = this.RGBAToArray(rgbString);

            function componentToHex(c) {
                let hex = c.toString(16);
                return hex.length == 1 ? "0" + hex : hex;
            }

            return "#" + componentToHex(parseInt(rgbArray[0])) + componentToHex(parseInt(rgbArray[1])) + componentToHex(parseInt(rgbArray[2]));
        },
        RGBAToArray: function(rgbaString) {
            return rgbaString.substring(rgbaString.indexOf('(') + 1, rgbaString.length - 1).split(', ');
        },
        resetWidth: function () {
            if(!this.block.widthEnabled) {
                this.$set(this.block.style, "width", "");
                this.$set(this.block.style, "flex", "1");
                this.$set(this.block.style, "flex-grow", "1");
                return;
            }

            this.block.style["flex"] = "none";
            this.$set(this.block.style, "width", this.getWidth() + this.widthUnit);
        }
    }
}
</script>