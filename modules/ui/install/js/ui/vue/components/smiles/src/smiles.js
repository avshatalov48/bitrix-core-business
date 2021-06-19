/**
 * Bitrix UI
 * Smiles Vue component
 *
 * @package bitrix
 * @subpackage ui
 * @copyright 2001-2020 Bitrix
 */

import "./smiles.css";
import 'ui.vue.directives.lazyload';

import {BitrixVue} from 'ui.vue';

import {SmileManager} from "./manager.js";
import {emoji} from './emoji';

BitrixVue.component('bx-smiles', {
	/**
	 * @emits 'selectSmile' {text: string}
	 * @emits 'selectSet' {setId: number}
	 */
	data()
	{
		return {
			smiles: [],
			sets: [],
			emoji: [],
			mode: 'smile',
			emojiIcon: '\uD83D\uDE0D',
		}
	},
	created()
	{
		this.setSelected = 0;
		this.serverLoad = false;

		let restClient = this.$root.$bitrixRestClient || this.$Bitrix.RestClient.get();

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
		});

		this.emoji = emoji;
	},
	methods:
	{
		selectSet(setId)
		{
			this.mode = "smile";
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
		},
		switchToEmoji()
		{
			this.mode = 'emoji';
			this.sets.map(set => {
				set.selected = false;
			});
		},
		showCategory(category)
		{
			if (this.isWindows())
			{
				return category.showForWindows;
			}
			else
			{
				return true;
			}
		},
		isMac()
		{
			return navigator.userAgent.toLowerCase().includes('macintosh');
		},
		isLinux()
		{
			return navigator.userAgent.toLowerCase().includes('linux');
		},
		isWindows()
		{
			return navigator.userAgent.toLowerCase().includes('windows') || (!this.isMac() && !this.isLinux());
		}
	},
	computed:
	{
		showEmoji()
		{
			return this.$Bitrix.Loc.getMessage('UTF_MODE') === 'Y';
		},
		isEmojiMode()
		{
			return this.mode === 'emoji';
		},
		isSmileMode()
		{
			return this.mode === "smile";
		},
		emojiIconStyle()
		{
			let style = 'bx-ui-smiles-set-emoji';
			if (this.isMac())
			{
				return style += '-mac';
			}
			else if (this.isLinux())
			{
				return style += '-linux';
			}
			else if (this.isWindows())
			{
				return style += '-win';
			}
			else
			{
				return style;
			}
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
				<template v-else-if="isSmileMode">
					<template v-for="smile in smiles">
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
				</template>
				<template v-else-if="isEmojiMode">
					<div v-for="category in emoji" class="bx-ui-smiles-emoji-wrap">
						<template v-if="showCategory(category)">
							<div class="bx-ui-smiles-category">
								{{ $Bitrix.Loc.getMessage('UI_VUE_SMILES_EMOJI_CATEGORY_' + category.code) }}
							</div>
							<template v-for="element in category.emoji">
								<div class="bx-ui-smiles-smile" style="font-size: 28px;">
									<div class="bx-ui-smiles-smile-icon" @click="selectSmile(element.symbol)">
										{{ element.symbol }}
									</div>
								</div>
							</template>
						</template>
					</div>
				</template>
			</div>
			<template v-if="sets.length > 1 || emoji && showEmoji">
				<div class="bx-ui-smiles-sets">
					<template v-for="set in sets">
						<div :class="['bx-ui-smiles-set', {'bx-ui-smiles-set-selected': set.selected}]">
							<img v-bx-lazyload
								:key="set.id"
								class="bx-ui-smiles-set-icon"
								:data-lazyload-src="set.image"
								data-lazyload-error-class="bx-ui-smiles-set-icon-error"
								:title="set.name"
								@click="selectSet(set.id)"
							/>
						</div>
					</template>
					<div v-if="emoji && showEmoji" :class="['bx-ui-smiles-set', {'bx-ui-smiles-set-selected': isEmojiMode}]">
						<div :class="['bx-ui-smiles-set-icon', emojiIconStyle]" @click="switchToEmoji">
						 	{{ emojiIcon }}
						</div>
					</div>
				</div>
			</template>
		</div>
	`
});
