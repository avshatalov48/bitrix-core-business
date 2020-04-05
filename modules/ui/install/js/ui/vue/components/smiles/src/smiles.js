/**
 * Bitrix UI
 * Smiles Vue component
 *
 * @package bitrix
 * @subpackage ui
 * @copyright 2001-2019 Bitrix
 */

import "./smiles.css";
import 'ui.vue.directives.lazyload';

import {SmileManager} from "./manager.js";
import {Vue} from 'ui.vue';
import {rest} from 'rest.client';

Vue.component('bx-smiles', {
	/**
	 * @emits 'selectSmile' {text: string}
	 * @emits 'selectSet' {setId: number}
	 */
	data()
	{
		return {
			smiles: [],
			sets: []
		}
	},
	created()
	{
		this.setSelected = 0;
		this.serverLoad = false;

		let restClient = this.$root.$bitrixRestClient || rest;

		this.smilesController = new SmileManager(restClient);
		this.smilesController.loadFromCache().then((result) => {
			if (this.serverLoad)
				return true;

			this.smiles = result.smiles;
			this.sets = result.sets.map((element, index) => {
				element.selected = this.setSelected === index;
				return element;
			});
		});

		this.smilesController.loadFromServer().then((result) => {
			this.smiles = result.smiles;
			this.sets = result.sets.map((element, index) => {
				element.selected = this.setSelected === index;
				return element;
			});
		})
	},
	methods:
	{
		selectSet(setId)
		{
			this.$emit('selectSet', {setId});

			this.smilesController.changeSet(setId).then((result) => {
				this.smiles = result;
				this.sets.map(set => {
					set.selected = set.id === setId;
					if (set.selected)
					{
						this.setSelected = setId;
					}
					return set;
				});
				this.$refs.elements.scrollTop = 0;
			});
		},
		selectSmile(text)
		{
			this.$emit('selectSmile', {text: ' '+text+' '});
		}
	},
	template: `
		<div class="bx-ui-smiles-box">
			<div class="bx-ui-smiles-elements-wrap" ref="elements">
				<template v-if="!smiles.length">
					<svg class="bx-ui-smiles-loading-circular" viewBox="25 25 50 50">
						<circle class="bx-ui-smiles-loading-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>
						<circle class="bx-ui-smiles-loading-inner-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>
					</svg>
				</template>
				<template v-else v-for="smile in smiles">
					<div class="bx-ui-smiles-smile">
						<img v-bx-lazyload :key="smile.id"
							class="bx-ui-smiles-smile-icon"
							:data-lazyload-src="smile.image"
							data-lazyload-error-class="bx-ui-smiles-smile-icon-error"
							:title="smile.name"
							:style="{height: (smile.originalHeight*0.5)+'px', width: (smile.originalWidth*0.5)+'px'}"
							@click="selectSmile(smile.typing)"
						/>
					</div>
				</template>
			</div>
			<template v-if="sets.length > 1">
				<div class="bx-ui-smiles-sets">
					<template v-for="set in sets">
						<div :class="['bx-ui-smiles-set', {'bx-ui-smiles-set-selected': set.selected}]">
							<img v-bx-lazyload :key="set.id"
								class="bx-ui-smiles-set-icon"
								:data-lazyload-src="set.image"
								data-lazyload-error-class="bx-ui-smiles-set-icon-error"
								:title="set.name"
								@click="selectSet(set.id)"
							/>
						</div>
					</template>
				</div>
			</template>
		</div>
	`
});