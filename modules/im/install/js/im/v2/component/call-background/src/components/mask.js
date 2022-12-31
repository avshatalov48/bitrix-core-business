import '../css/mask.css';

import {Mask} from '../classes/items/mask';

// @vue/component
export const MaskComponent = {
	props:
	{
		element: {
			type: Object,
			required: true
		},
		isSelected: {
			type: Boolean,
			required: true
		}
	},
	data()
	{
		return {};
	},
	computed:
	{
		mask(): Mask
		{
			return this.element;
		},
		containerClasses(): string[]
		{
			const classes = [`--${this.mask.id}`];
			if (this.isSelected)
			{
				classes.push('--selected');
			}

			if (!this.mask.active)
			{
				classes.push('--inactive');
			}

			return classes;
		},
		imageStyle(): {backgroundImage: string}
		{
			let backgroundImage = '';
			if (this.mask.preview)
			{
				backgroundImage = `url('${this.mask.preview}')`;
			}

			return {backgroundImage};
		}
	},
	methods:
	{
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		}
	},
	template: `
		<div :class="containerClasses" class="bx-im-call-background__item --mask">
			<div v-if="!mask.active" class="bx-im-call-background__mask_fade"></div>
			<div class="bx-im-call-background__mask_background"></div>
			<div :style="imageStyle" class="bx-im-call-background__item_image"></div>
			<div v-if="mask.isLoading" class="bx-im-call-background__mask_loading-container">
				<div class="bx-im-call-background__mask_loading-icon"></div>
				<div class="bx-im-call-background__mask_loading-text">{{ loc('BX_IM_CALL_BG_MASK_LOADING') }}</div>
			</div>
			<div v-else-if="!mask.active" class="bx-im-call-background__mask_soon-container">
				<div class="bx-im-call-background__mask_soon-text">{{ loc('BX_IM_CALL_BG_MASK_COMING_SOON') }}</div>
			</div>
			<div v-else class="bx-im-call-background__mask_title">{{ mask.title }}</div>
		</div>
	`
};