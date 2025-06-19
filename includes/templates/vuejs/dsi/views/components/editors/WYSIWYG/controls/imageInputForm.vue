<template>
    <div>
        <div>
            <h3>{{ messages.get('dsi', 'view_wysiwyg_input_image') }}</h3>
            <div v-if="block.content == '' || block.content.startsWith('data:')" class="dsi-form-group dsi-form-wysiwyg">
                <label class="etiquette" for="image">{{ messages.get('dsi', 'view_wysiwyg_input_file') }}</label>
                <div class="dsi-form-group-content">
                    <div class="dsi-form-group-line">
                        <input v-if="block.content == ''" @change="changeImage" type="file"
                            id="image" name="image"
                            accept="image/png, image/jpeg" />
                        <div v-else class="wysiwyg-bg-image-preview">
                            <img width="48" height="48" :src="block.content" alt="" />
                        </div>
                        <button style="cursor: pointer;" v-if="block.content != ''" type="button" @click="block.content = ''">
                            <i class="fa fa-times" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div v-if="! block.content.startsWith('data:')" class="dsi-form-group dsi-form-wysiwyg">
                <label class="etiquette" for="content">{{ messages.get('dsi', 'view_wysiwyg_input_url') }}</label>
                <div class="dsi-form-group-content">
                    <div class="dsi-form-group-line">
                        <input name="content" type="text" v-model.lazy="block.content" />
                        <button style="cursor: pointer;" class="right" v-if="block.content != ''" type="button" @click="block.content = ''">
                            <i class="fa fa-times" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="dsi-form-group dsi-form-wysiwyg">
                <label class="etiquette" for="alt">{{ messages.get('dsi', 'view_wysiwyg_input_image_alt') }}</label>
                <div class="dsi-form-group-content">
                    <div class="dsi-form-group-line">
                        <input name="alt" type="text" v-model="block.alt" />
                    </div>
                </div>
            </div>
            <div class="dsi-form-group dsi-form-wysiwyg">
                <label class="etiquette" for="redirect">{{ messages.get('dsi', 'dsi_wysiwyg_image_redirect') }}</label>
                <div class="dsi-form-group-content">
                    <div class="dsi-form-group-line">
                        <input name="redirect" type="url" v-model="block.redirect" />
                    </div>
                </div>
            </div>
            <multimedia-input-form :block="block" element-type="image"></multimedia-input-form>
        </div>
    </div>
</template>

<script>
import multimediaInputForm from './multimediaInputForm.vue';
export default {
    name : "imageInputForm",
    props : ['block'],
    components : {
        multimediaInputForm
    },
    methods : {
        changeImage(event) {
            var files = event.target.files || event.dataTransfer.files;
            if (!files.length) return;
            this.createImage(files[0]);
        },
        createImage(file) {
            var image = new Image();
            var reader = new FileReader();

            reader.onload = (e) => {
                image = e.target.result;
                this.$set(this.block, "content", image);
            };
            reader.readAsDataURL(file);
        }
    }
}
</script>