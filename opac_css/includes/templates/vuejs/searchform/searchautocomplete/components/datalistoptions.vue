<template>
	<datalist :id="datalistId" :style="datastyle" class="datalist" @mouseover="$emit('reset-index')">
		<loader v-if="showLoader" :height="height"></loader>
		 <span v-else v-for="(item, index) in items"  :value="item.value" :key="index" style="display:inline-block; height : 100%; width:100%; margin-top : 3px; margin-bottom:3px;">
			<option
				:class="`datalist-option ${index == datalistIndex ? 'datalist-option-active' : ''}`" 
				:value="item.value"
				@click.self="$emit('update-label', item.value)">
				<span 
					@click.self="$emit('update-label', item.value)" 
					:title="item.label" 
					class="datalist-label">{{item.label}}</span>
				<input v-if="item.link" 
					type="button" 
					@mousedown="redirect(item.link)" 
					class="bouton" 
					:value="pmb.getMessage('search_autocomplete', 'see_entity')" />
			</option>
		 </span>
	</datalist>
</template>

<script>
import loader from './loader.vue';

export default {
	name: "datalistoptions",
	props : [
	    'datalistId',
	    'items',
	    'inputId',
		'datalistIndex',
		'showDatalist',
		'showLoader'
    ],
	components : {
		loader
	},
	mounted : function() {
		this.inputWidth = this.getWidth();
	},
	data : function() {
		return  {
			inputWidth : "",
			height : 50
		}
	},
	watch : {
		items : {
			handler : function() {
				let datalist = document.getElementById(this.datalistId);
				if(datalist != null && datalist.offsetHeight > 50) {
					setTimeout(()=> this.height = datalist.offsetHeight, 10);
				} else {
					this.height = 50;
				}
			},
		}
	},
	computed: {
        datastyle : function() {
            let show = {
				display : "block", 
				width : this.inputWidth, 
				inlineSize : this.inputWidth,
			};
            let hide = {
				display : "none", 
				width : this.inputWidth, 
				inlineSize : this.inputWidth,
			};
            if((this.showDatalist && this.items.length) || this.showLoader) {
                let input = document.getElementById(this.inputId);
                if(input == document.activeElement) {
                    return show;
                }
            }
            return hide;
        }
	},
	methods :{
		redirect : function(link) {
			document.location = link;
		},
		getWidth : function() {
			let input = document.getElementById(this.inputId);
			if(input !== null) {
				return input.clientWidth + "px";
			}
		}
	}
}
</script>

<style>
.datalist {
	position: absolute;
	background-color: white;
	border: 1px solid #d2d2d2;
	border-radius: 0 0 5px 5px;
	border-top: none;
	padding: 2px;
	overflow: auto;
	z-index: 500;
	overflow-wrap: break-word;
}

.datalist-option {
	display : flex;
	justify-content: space-between;
	align-items: center;
	padding-left: 3px;
	padding-right: 3px;
	border-radius: 3px;
	cursor: pointer;
}

.datalist-option:hover,  .datalist-option-active{
	background-color: lightblue;
}

.datalist-hr {
	grid-column: 1 / -1;
	margin : 0 !important;
	margin-bottom: 10px !important;
}

.datalist-label {
	width : 90%;
	overflow: hidden;
	text-overflow: ellipsis;
	text-align: left;
}
</style>