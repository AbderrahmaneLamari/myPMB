<template>
    <div>
        <!-- <button type="button" class="bouton" @click="showForm = !showForm">
            <i :class="showForm ? 'fa fa-minus' : 'fa fa-plus'" aria-hidden="true"></i>
        </button> -->

        <div class="form-view-configuration">
            <div class="form-view-configuration-method">
                <button 
                    :class="methodSelected == 1 ? 'form-view-configuration-btn active' : 'form-view-configuration-btn'"
                    type="button"
                    @click="methodSelected = 1"
                    :title="!modelList.length ? messages.get('dsi', 'view_configuration_no_model_title') : ''"
                    :disabled="!modelList.length">
    
                    <i class="fa fa-download" aria-hidden="true"></i>
                    {{ messages.get("dsi", "view_configuration_existing_model") }}
                </button>
                <button 
                    :class="methodSelected == 2 ? 'form-view-configuration-btn active' : 'form-view-configuration-btn'"
                    type="button"
                    @click="methodSelected = 2">
    
                    <i class="fa fa-plus-circle" aria-hidden="true"></i>
                    {{ messages.get("dsi", "view_configuration_new") }}
                </button>
            </div>

            <!-- <div v-if="isModel">
                <label class="etiquette" for="view-name">{{ messages.get('dsi', 'view_form_name') }}</label>
                <input type="text" id="view-name" name="view-name" v-model="view.name" required>
            </div> -->

            <div class="form-view-configuration-row">
                <!-- Level 1 -->
                <div class="form-view-configuration-element">
                    <button v-for="(level, index) in levels['level_1']" :key="index"
                        type="button"
                        :class="getClassBtn(1, level.value)"
                        @click="updateSelectedLevels(1, level.value)">
        
                        {{ level.label }}
                    </button>
                    <span :title="getTitle(1)">
                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                    </span>
                </div>

                <!-- Level 2 -->
                <div v-if="!isModel" class="form-view-configuration-element">
                    <button v-for="(level, index) in levels['level_2']" :key="index"
                        type="button"
                        :class="getClassBtn(2, level.value)"
                        @click="updateSelectedLevels(2, level.value)">
        
                        {{ level.label }}
                    </button>
                    <span :title="getTitle(2)">
                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                    </span>
                </div>
            </div>

            <!-- Level 3 -->
            <div class="form-view-configuration-row">
                <div class="form-view-configuration-element">
                    <button v-for="(level, index) in levels['level_3']" :key="index"
                        type="button"
                        :class="getClassBtn(3, level.value)"
                        @click="updateSelectedLevels(3, level.value)">
        
                        {{ level.label }}
                    </button>
                    <span :title="getTitle(3)">
                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                    </span>
                </div>
            </div>

            <!-- Level 4 -->
            <div v-if="levels['level_4']" class="form-view-configuration-row">
                <div class="form-view-configuration-element">
                    <button v-for="(level, index) in levels['level_4']" :key="index"
                        type="button"
                        :class="getClassBtn(4, level.value)"
                        :disabled="!isAvailableEntity(level)"
                        @click="updateSelectedLevels(4, level.value)">
        
                        {{ level.label }}
                    </button>
                </div>
            </div>

            <formViewSelector 
                :elements="elements"
                :selectedElement="elementSelected" 
                :view="view" 
                @clickedElement="changeElement">
            </formViewSelector>
        </div>

        <button type="button" class="bouton" :disabled="!elementSelected" @click="selectElement">
            {{ messages.get("dsi", "view_configuration_select_btn") }}
        </button>
    </div>
</template>

<script>
    import formViewSelector from './formViewSelector.vue';

    export default {
        props: ["view", "types", "entities", "isModel", "channelCompatibility"],
        components: {
            formViewSelector
        },
        data: function () {
            return {
                // Element selectionn�
                elementSelected: "",

                // Liste des niveaux
                levels: [],

                // Liste des niveaux selectionn�s
                levelsSelected: {
                    "level_1": [],
                    "level_2": [],
                    "level_3": [],
                    "level_4": ["all_type"]
                },

                // M�thode de cr�ation de vue selectionn�e
                methodSelected: 1,

                // Liste des niveaux � s�lection unique
                uniqueLevelsSelected: ["level_1", "level_2", "level_3"],

                
                modelList: []
            }
        },
        created: async function () {
            await this.getLevels();
            await this.getModelList();

            this.initSelectedLevels();

            this.$root.$on('openViewConfiguration', (method) => {
                this.methodSelected = method;
                //this.initSelectedLevels();
                this.$parent.show();
            });
        },
        computed: {
            /**
             * Retourne les �l�ments en fonction de la m�thode s�lectionn�e.
             *
             * @return {Array} Les �l�ments filtr�s.
             */
            elements: function() {
                if(this.methodSelected == 1) {
                    return this.filteredModelList;
                }

                return this.filteredViewTypesList;
            },

            /**
             * Retourne une liste filtr�e de mod�les.
             *
             * @return {Array} La liste filtr�e de mod�les.
             */
            filteredModelList: function() {
                let list = [];

                this.modelList.forEach(model => {
                    const found = this.findTypeById(model.type);
                    if(found) {
                        list.push({
                            id: model.id,
                            label: model.name,
                            value: found.id,
                            icon: model.settings.image ? model.settings.image : found.default_model_image 
                        });
                    }
                });

                return this.filterElementList(list);
            },

            /**
             * G�n�re une liste filtr�e des types de vue.
             *
             * @return {Array} La liste filtr�e des types de vue.
             */
            filteredViewTypesList: function() {
                let list = [];

                this.types.forEach(type => {
                    list.push({ label: type.name, value: type.id, icon: type.default_model_image });
                });

                return this.filterElementList(list);
            }
        },
        methods: {
            /**
             * R�cup�re les niveaux
             *
             * @return {Promise<void>}
             */
            getLevels: async function() {
                let levels = await this.ws.get("views", "getLevels");

                if (levels.error) {
                    this.notif.error(response.messages);
                    return;
                }

                this.$set(this, "levels", levels);

                if(!this.levels["level_4"]) {
                    this.$set(this.levels, "level_4", []);
                }
            },

            /**
             * Initialise les niveaux s�lectionn�s.
             * 
             * @return {void}
             */
            initSelectedLevels: function() {
                if(!this.view.numModel && !this.findModelById(this.view.numModel)) {
                    this.$set(this, "methodSelected", 2);
                }

                let currentType = this.findTypeById(this.view.type);
                if(currentType && currentType.levels) {
                    const clone = JSON.parse(JSON.stringify(currentType));

                    if(this.isModel) {
                        clone.levels["level_2"] = [];
                    }

                    if(!clone.levels["level_4"]) {
                        clone.levels["level_4"] = ["all_type"];
                    }

                    this.$set(this, "levelsSelected", clone.levels);
                }
            },

            /**
             * Met � jour les niveaux s�lectionn�s en fonction du niveau et de la valeur donn�s en entr�e.
             *
             * @param {number} level - Le niveau � mettre � jour.
             * @param {string} value - La valeur � mettre � jour.
             */
            updateSelectedLevels: function(level, value) {
                if((this.uniqueLevelsSelected.includes(`level_${level}`) && !this.levelsSelected[`level_${level}`].includes(value)) || value == "all_type") {
                    this.levelsSelected[`level_${level}`] = [];
                }

                if(this.levelsSelected[`level_${level}`].includes(value)) {
                    this.levelsSelected[`level_${level}`].splice(this.levelsSelected[`level_${level}`].indexOf(value), 1);
                    return;
                }

                if(this.levelsSelected["level_4"].includes("all_type") && level == 4 && value != "all_type") {
                    this.levelsSelected["level_4"].splice(this.levelsSelected["level_4"].indexOf("all_type"), 1);
                }

                this.levelsSelected[`level_${level}`].push(value);
            },

            /**
             * Renvoie la classe CSS du bouton en fonction du niveau et de la valeur donn�s.
             *
             * @param {number} level - Le niveau du bouton.
             * @param {any} value - La valeur du bouton.
             * @return {string} La classe CSS du bouton.
             */
            getClassBtn: function(level, value) {
                if(this.levelsSelected[`level_${level}`] && this.levelsSelected[`level_${level}`].includes(value)) {
                    return "form-view-configuration-btn active";
                }

                return "form-view-configuration-btn";
            },

            /**
             * R�cup�re de mani�re asynchrone la liste des mod�les
             *
             * @return {Promise<void>}
             */
            getModelList: async function () {
                let list = await this.ws.get("views", "getModels");
                if(list.error) {
                    this.notif.error(list.messages);
                    return;
                }

                if(this.isModel) {
                    list = list.filter(model => model.id != this.view.id);
                }

                this.$set(this, "modelList", list);
            },

            /**
             * Trouve un mod�le par son id.
             *
             * @param {number} id - L'id du mod�le.
             * @return {object} Le mod�le trouv�, ou false s'il n'est pas trouv�.
             */
            findModelById: function(id) {
                const found = this.modelList.find(model => model.id == id);
                if(found) {
                    return found;
                }

                return false;
            },

            /**
             * Trouve un type de vue par son id.
             *
             * @param {number} id - L'id du type de vue.
             * @return {object} Le type de vue trouv�, ou false s'il n'est pas trouv�.
             */
            findTypeById: function(id) {
                const found = this.types.find(type => type.id == id);
                if(found) {
                    return found;
                }

                return false
            },

            /**
             * Met � jour la vue avec l'�l�ment fourni.
             *
             * @param {Object} 
             */
            updateView: function() {
                if(this.methodSelected == 2) {
                    this.$set(this.view, "type", this.elementSelected.value);
                    return;
                }

                const model = this.findModelById(this.elementSelected.id);
                if(model) {
                    let clone = JSON.parse(JSON.stringify(model));
					const ignoreKeys = this.Const.views.ignoredKeys;
					for(let property in clone) {
						if(ignoreKeys.indexOf(property) == -1) {
							this.$set(this.view, property, clone[property]);
						}
					}

					this.$set(this.view, "numModel", clone["id"]);
                }
            },

            /**
             * Filtre la liste donn�e d'�l�ments en fonction de certaines conditions.
             *
             * @param {Array} list - La liste d'�l�ments � filtrer.
             * @return {Array} - La liste filtr�e d'�l�ments.
             */
            filterElementList: function(list) {
                return list.filter(element => {
                    const elementType = this.findTypeById(element.value);
                    if(elementType) {
                        for (const [key, level] of Object.entries(this.levelsSelected)) {
                            for(const levelElement of level) {
                                if(key === "level_4") {
                                    if(levelElement == "all_type") {
                                        return true;
                                    }

                                    // Compatibilit�e avec les donn�es
                                    if(elementType.compatibility && elementType.compatibility.item && elementType.compatibility.item.includes(levelElement)) {
                                        return true;
                                    }
                                }

                                if(!elementType.levels || !elementType.levels[key] || !elementType.levels[key].includes(levelElement)) {
                                    return false;
                                }
                            }
                        }

                        // Compatibilit�e avec les canaux
                        if(this.channelCompatibility && !this.channelCompatibility.compatibility.view.includes(elementType.namespace)) {
                            return false;
                        }

                        return true;
                    }
                });
            },

            /**
             * D�termine si l'entit� est disponible en fonction du niveau donn�.
             *
             * @param {number} level - Le niveau de l'entit�.
             * @return {boolean} - Renvoie true si l'entit� est disponible, false sinon.
             */
            isAvailableEntity: function(level) {
                if(level.value == "all_type") {
                    return true;
                }

                for (const [key, entity] of Object.entries(this.entities)) {
                    if(entity == level.label) {
                        return this.Const.items.availableItemSources.includes(parseInt(key));
                    }
                }

                return false;
            },

            /**
             * Change l'�l�ment s�lectionn� pour l'�l�ment donn�.
             *
             * @param {type} element - Le nouvel �l�ment � s�lectionner.
             * @return {void}
             */
            changeElement: function(element) {
                this.elementSelected = element;
            },

            /**
             * Met � jour la vue si un �l�ment est s�lectionn�.
             *
             * @return {void}
             */
            selectElement: function() {
                if(this.elementSelected) {
                    this.updateView();
                    this.$parent.close();
                }
            },

            /**
             * Retourne le titre pour un niveau donn�.
             *
             * @param {number} level - Le niveau pour lequel obtenir le titre.
             * @return {string} Le titre pour le niveau donn�.
             */
            getTitle: function(level) {
                let title = "";

                if(this.levels[`level_${level}`]) {
                    this.levels[`level_${level}`].forEach(element => {
                        const msgContent = this.messages.get("dsi", `view_configuration_level_${level}_${element.value}_desc`);
    
                        title += `${element.label} : ${msgContent} \n`;
                    });
                }

                return title;
            }
        }
    }
</script>