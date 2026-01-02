/**
 * Bitrix UI
 * Smiles Vue3 component
 *
 * @package bitrix
 * @subpackage ui
 * @copyright 2001-2021 Bitrix
 */

import './smiles.css';
import { lazyload } from 'ui.vue3.directives.lazyload';
import { SmileManager } from './manager.js';
import { EmojiTab } from './tabs/emoji-tab';
import { LoadingTab } from './tabs/loading-tab';
import { SmilesSetTab } from './tabs/smiles-set-tab';
import { isLinux, isMac, isWindows } from './utils';

// @vue/component
export const Smiles = {
	// eslint-disable-next-line vue/multi-word-component-names
	name: 'Smiles',
	components: {
		LoadingTab,
		SmilesSetTab,
		EmojiTab,
	},
	directives: {
		lazyload,
	},
	props: {
		isOnlyEmoji: {
			type: Boolean,
			default: false,
		},
	},
	emits: ['selectSmile', 'selectSet'],
	data(): Object
	{
		return {
			smiles: [],
			sets: [],
			setSelected: 0,
			mode: 'smile',
			emojiIcon: '\uD83D\uDE0D',
		};
	},
	computed:
	{
		isShowEmoji(): boolean
		{
			return this.$Bitrix.Loc.getMessage('UTF_MODE') === 'Y';
		},
		isShowSmiles(): boolean
		{
			return !this.isOnlyEmoji;
		},
		isLoading(): boolean
		{
			return this.isShowSmiles && this.smiles.length <= 0;
		},
		isEmojiMode(): boolean
		{
			return this.mode === 'emoji';
		},
		isSmileMode(): boolean
		{
			return this.mode === 'smile';
		},
		// more than one tab basically
		isShowTabsSelector(): boolean
		{
			if (this.isShowSmiles && this.sets.length > 1)
			{
				return true;
			}

			return this.isShowSmiles && this.isShowEmoji;
		},
		emojiIconStyle(): string
		{
			const style = 'bx-ui-smiles-set-emoji';
			if (isMac())
			{
				return `${style}-mac`;
			}

			if (isLinux())
			{
				return `${style}-linux`;
			}

			if (isWindows())
			{
				return `${style}-win`;
			}

			return style;
		},
	},
	created()
	{
		if (this.isShowSmiles)
		{
			this.mode = 'smile';
		}
		else if (this.isShowEmoji)
		{
			this.mode = 'emoji';
		}

		if (this.isShowSmiles)
		{
			this.smilesController = new SmileManager(this.$Bitrix.RestClient.get());
			void this.smilesController.loadFromCache().then((result) => {
				this.smiles = result.smiles;
				this.sets = result.sets.map((element, index) => {
					// eslint-disable-next-line no-param-reassign
					element.selected = this.setSelected === index;

					return element;
				});
			});

			void this.smilesController.loadFromServer().then((result) => {
				this.smiles = result.smiles;
				this.sets = result.sets.map((element, index) => {
					// eslint-disable-next-line no-param-reassign
					element.selected = this.setSelected === index;

					return element;
				});
			});
		}
	},
	methods:
	{
		selectSet(setId)
		{
			if (!this.isShowSmiles)
			{
				return;
			}

			this.mode = 'smile';
			this.$emit('selectSet', { setId });

			void this.smilesController.changeSet(setId).then((result) => {
				this.smiles = result;
				this.sets.map((set) => {
					// eslint-disable-next-line no-param-reassign
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
		selectSmile(event)
		{
			this.$emit('selectSmile', { text: ` ${event.text} ` });
		},
		switchToEmoji()
		{
			if (!this.isShowEmoji)
			{
				return;
			}

			this.mode = 'emoji';
			this.sets.forEach((set) => {
				// eslint-disable-next-line no-param-reassign
				set.selected = false;
			});
		},
	},
	// language=Vue
	template: `
		<div class="bx-ui-smiles-box">
			<div class="bx-ui-smiles-elements-wrap" ref="elements">
				<LoadingTab v-if="isLoading"/>
				<SmilesSetTab v-else-if="isSmileMode" :smiles="smiles" @selectSmile="selectSmile"/>
				<EmojiTab v-else-if="isEmojiMode" @selectSmile="selectSmile"/>
			</div>
			<template v-if="isShowTabsSelector">
				<div class="bx-ui-smiles-sets">
					<template v-if="isShowSmiles">
						<template v-for="set in sets">
							<div :class="['bx-ui-smiles-set', {'bx-ui-smiles-set-selected': set.selected}]">
								<img v-lazyload
									 :key="set.id"
									 class="bx-ui-smiles-set-icon"
									 :data-lazyload-src="set.image"
									 data-lazyload-error-class="bx-ui-smiles-set-icon-error"
									 :title="set.name"
									 @click="selectSet(set.id)"
								/>
							</div>
						</template>
					</template>
					<div v-if="isShowEmoji" :class="[
						'bx-ui-smiles-set',
						{
							'bx-ui-smiles-set-selected': isEmojiMode,
						},
					]">
						<div :class="['bx-ui-smiles-set-icon', emojiIconStyle]" @click="switchToEmoji">
							{{ emojiIcon }}
						</div>
					</div>
				</div>
			</template>
		</div>
	`,
};
