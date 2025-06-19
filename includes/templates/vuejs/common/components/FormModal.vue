<template>
    <form :id="formModalId" action="#" :class="formClass" method="POST" @submit.prevent="submit">
        <Modal ref="modal" :title="title" @close="dispatchEvent('close', $event)">
            <slot></slot>
            <template v-slot:footer>
                <div class="row" v-if="showAction">
                    <button type="button" class="bouton left" @click="close">
                        {{ messages.get('common', 'cancel') }}
                    </button>
                    <button type="submit" class="bouton right">
                        {{ messages.get('common', 'submit') }}
                    </button>
                </div>
            </template>
        </Modal>
    </form>
</template>

<script>
import Modal from './Modal.vue';

let uid = 0;
export default {
    components: {
        Modal
    },
    props: {
        formClass: {
            default: () => ''
        },
        showAction: {
            type: Boolean,
            default: () => true
        },
        title: {
            type: String,
            default: () => ''
        }
    },
    data: function () {
        return {
            id: 0
        }
    },
    computed: {
        formModalId: function () {
            return `form-modal-${this.id}`;
        }
    },
    create: function () {
        this.id = uid;
        uid++;
    },
    methods: {
        show: function () {
            this.$refs.modal.show();
            this.dispatchEvent('show');
        },
        close: function () {
            this.$refs.modal.close();
            this.dispatchEvent('close');
        },
        dispatchEvent: function (event, data) {
            this.$emit(event, data || undefined);
        },
        submit: function () {
            const form = document.getElementById(this.formModalId);
            if (form) {
                const formData = new FormData(form);
                const formDataObj = {};
                formData.forEach((value, key) => (formDataObj[key] = value));
                this.dispatchEvent("submit", formDataObj);
            }
        }
    }
}
</script>