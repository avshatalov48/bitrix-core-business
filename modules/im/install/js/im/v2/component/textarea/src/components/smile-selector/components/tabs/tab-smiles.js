import {Loc} from 'main.core';
import {EventEmitter} from 'main.core.events';

import {EventType} from 'im.v2.const';
import {SmileManager, type Set, type Smile} from 'im.v2.lib.smile-manager';
import {Utils} from 'im.v2.lib.utils';

import {emoji, defaultEmojiIcon} from '../../emoji';

import '../../../../css/smile-selector/tabs/tab-smiles.css';

export type SmilesConfig = {
	smiles: Array<Smile>;
	sets: Array<Set>;
	selectedSetId: String;
};

// @vue/component
export const TabSmiles = {
	name: 'SmilesContent',
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
	},
	emits: ['close'],
	data(): SmilesConfig
	{
		return {
			smiles: [],
			sets: [],
			recentEmoji: new Set(),
			selectedSetId: ''
		};
	},
	computed:
	{
		categoryTitles(): {[key: string]: string}
		{
			const categoryTitles = emoji.reduce((acc, category) => {
				const prefix = `IM_TEXTAREA_EMOJI_CATEGORY_`;
				const title = Loc.getMessage(`${prefix}${category.code}`);

				return {...acc, [category.code]: title};
			}, {});
			categoryTitles[this.frequentlyUsedLoc] = Loc.getMessage(this.frequentlyUsedLoc);

			return categoryTitles;
		},
		visibleSmiles(): Array<Smile>
		{
			const smiles = this.smiles.filter((smile) => {
				return smile.setId === this.selectedSetId && smile.alternative !== false;
			});

			return smiles;
		},
		visibleRecentEmoji(): Array<String>
		{
			const emoji = [...this.recentEmoji];

			return emoji.slice(0, this.maxRecentEmoji);
		},
		lastSelectedSetId(): string
		{
			const set = this.sets.find((set) => {
				return !!set.selected;
			});
			if (!set)
			{
				return this.emojiSetTitle;
			}

			return set.id;
		}
	},
	created()
	{
		const smileManager = SmileManager.getInstance();
		if (!smileManager.smileList)
		{
			return;
		}

		const {sets, smiles} = smileManager.smileList;
		this.sets = sets;
		this.smiles = smiles;
		this.emojiSetTitle = 'emoji';
		this.selectedSetId = this.lastSelectedSetId;
		this.emoji = emoji;
		this.recentEmoji = new Set(smileManager.recentEmoji);
		this.defaultEmojiIcon = defaultEmojiIcon;
		this.maxRecentEmoji = 18;
		this.frequentlyUsedLoc = 'IM_TEXTAREA_EMOJI_CATEGORY_FREQUENTLY';
	},
	beforeUnmount()
	{
		const smileManager = SmileManager.getInstance();
		if (this.lastSelectedSetId !== this.selectedSetId)
		{
			smileManager.updateSelectedSet(this.selectedSetId);
		}
		if (this.visibleRecentEmoji.length > smileManager.recentEmoji.size)
		{
			smileManager.updateRecentEmoji(new Set(this.recentEmoji));
		}
	},
	methods:
	{
		calculateRatioSize(smile: Smile): {width: string; height: string;}
		{
			const ratio = 1.75;
			const width = `${smile.width * ratio}px`;
			const height = `${smile.height * ratio}px`;

			return {width, height};
		},
		onSmileClick(smileCode: string, event: PointerEvent)
		{
			EventEmitter.emit(EventType.textarea.insertText, {
				text: smileCode,
				dialogId: this.dialogId,
			});
			if (!Utils.key.isAltOrOption(event))
			{
				this.$emit('close');
			}
		},
		onEmojiClick(emojiText: string, event: PointerEvent)
		{
			this.onSmileClick(emojiText, event);
			this.addEmojiToRecent(emojiText);
		},
		addEmojiToRecent(symbol: string)
		{
			this.recentEmoji.add(symbol);
		}
	},
	template: `
		<div class="bx-im-smiles-content__scope">
			<div class="bx-im-smiles-content__smiles-box">
				<img
					v-for="smile in visibleSmiles"
					:key="smile.id"
					:src="smile.image"
					:title="smile.name ?? smile.typing"
					:style="calculateRatioSize(smile)"
					:alt="smile.typing"
					class="bx-im-smiles-content__smiles-box_smile"
					@click="onSmileClick(smile.typing, $event)"
				/>
				<template v-if="visibleSmiles.length === 0 && selectedSetId === emojiSetTitle">
					<div
						v-if="recentEmoji.size > 0"
						class="bx-im-smiles-content__smiles-box_category"
						key="frequently-used"
					>
						<p class="bx-im-smiles-content__smiles-box_category-title">
							{{categoryTitles[frequentlyUsedLoc]}}
						</p>
						<span
							v-for="symbol in visibleRecentEmoji"
							class="bx-im-smiles-content__smiles-box_category-emoji"
							role="img"
							:key="'recent-'+ symbol"
							@click="onSmileClick(symbol, $event)"
						>
							{{symbol}}
						</span>
					</div>
					<div
						v-for="category in emoji"
						:key="category.id"
						class="bx-im-smiles-content__smiles-box_category"
					>
						<template v-if="category.showForWindows ?? true">
							<p class="bx-im-smiles-content__smiles-box_category-title">
								{{categoryTitles[category.code]}}
							</p>
							<span
								v-for="element in category.emoji"
								:key="element.symbol"
								class="bx-im-smiles-content__smiles-box_category-emoji"
								role="img"
								@click="onEmojiClick(element.symbol, $event)"
							>
								{{element.symbol}}
							</span>
						</template>
					</div>
				</template>
			</div>
			<div class="bx-im-smiles-content__sets">
				<span
					v-for="set in sets" :key="set.id"
					class="bx-im-smiles-content__sets_set --img"
					:class="{
						'--selected': selectedSetId === set.id
					}"
					:title="set.name"
					@click="selectedSetId = set.id"
				>
					<img :src="set.image" />
				</span>
				<span
					class="bx-im-smiles-content__sets_set --emoji"
					:class="{
						'--selected': selectedSetId === emojiSetTitle
					}"
					@click="selectedSetId = emojiSetTitle"
				>
					{{defaultEmojiIcon}}
				</span>
			</div>
		</div>
	`
};