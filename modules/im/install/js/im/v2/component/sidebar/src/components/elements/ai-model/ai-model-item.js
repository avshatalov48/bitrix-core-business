// @vue/component
export const AiModelItem = {
	name: 'AiModelItem',
	props:
	{
		text: {
			type: String,
			required: true,
		},
		icon: {
			type: String,
			default: '',
		},
		selected: {
			type: Boolean,
			default: false,
		},
	},
	computed:
	{
		iconClass(): string[]
		{
			return ['bx-im-ai-model-popup-content__item_icon', `--${this.icon}`];
		},
	},
	template: `
		<div class="bx-im-ai-model-popup-content__item">
			<template v-if="icon">
				<div :class="iconClass"></div>
			</template>
			<div class="--line-clamp-2">{{ text }}</div>
			<template v-if="selected">
				<div class="bx-im-ai-model-popup-content__item_icon --check"></div>
			</template>
		</div>
	`,
};
