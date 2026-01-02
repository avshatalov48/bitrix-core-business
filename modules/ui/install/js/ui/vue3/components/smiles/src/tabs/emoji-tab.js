import { emoji } from '../emoji';
import { isWindows } from '../utils';

// @vue/component
export const EmojiTab = {
	name: 'EmojiTab',
	emits: ['selectSmile'],
	data(): Object
	{
		return {
			emoji: [],
		};
	},
	created()
	{
		this.emoji = emoji;
	},
	methods:
	{
		selectSmile(text)
		{
			this.$emit('selectSmile', { text });
		},
		isShowCategory(category): boolean
		{
			if (isWindows())
			{
				return category.showForWindows;
			}

			return true;
		},
	},
	template: `
		<div v-for="category in emoji" class="bx-ui-smiles-emoji-wrap">
			<template v-if="isShowCategory(category)">
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
	`,
};
