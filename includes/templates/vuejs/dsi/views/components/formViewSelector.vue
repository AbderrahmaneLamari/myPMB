<template>
    <div class="form-view-selector-type">
        <div v-for="(element, index) in elements" :key="index" class="form-view-selector-type-element">
            <button type="button" :class="getClassSelected(element.value)" @click="clickedElement(element)">
                <img :src="getSrcIcon(element.icon)" alt="">
                <span>{{ element.label }}</span>
            </button>
        </div>

        <div v-if="!elements.length" class="form-view-selector-type-element">
            <span>{{ messages.get("dsi", "view_configuration_empty_selector") }}</span>
        </div>
    </div>
</template>

<script>
    export default {
        props: ["elements", "elementSelected", "view"],
        data: function() {
            return {
                element: this.elementSelected
            }
        },
        methods: {
            /**
             * G�re l'�v�nement de clic sur un �l�ment.
             *
             * @param {Object} element - L'�l�ment qui a �t� cliqu�.
             * @return {void}
             */
            clickedElement: function(element) {
                this.element = element;
                this.$emit("clickedElement", element);
            },

            /**
             * Retourne la classe "selected" si la valeur correspond au type de la vue, sinon retourne une cha�ne vide.
             *
             * @param {type} value - La valeur � comparer avec le type de la vue actuel.
             * @return {String} - La classe "selected" ou une cha�ne vide.
             */
            getClassSelected: function(value) {
                if(this.element) {
                    if(this.element.value == value) {
                        return "selected"
                    }
                }

                return "";
            },

            /**
             * Retourne l'ic�ne source en fonction du param�tre ic�ne donn�.
             *
             * @param {string} ic�ne - L'ic�ne � v�rifier.
             * @return {string} L'ic�ne source ou le chemin vers l'ic�ne.
             */
            getSrcIcon: function(icon) {
                const regex = /^data:image\/(png|jpeg|gif|webp);base64,/;
                if (icon && regex.test(icon)) {
                    return icon;
                }

                return './' + icon;
            }

        }
    };
</script>