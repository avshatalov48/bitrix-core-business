import { Text } from 'main.core';

import './css/radio.css';

export type RadioOptionItem = {
	value: OptionValue,
	text: string,
	selected: boolean,
	subtext?: string,
	html?: boolean,
};

type OptionValue = string | number | boolean;

// @vue/component
export const RadioOption = {
	name: 'RadioOption',
	props:
	{
		items: {
			type: Array,
			required: true,
		},
	},
	emits: ['change'],
	data(): { groupName: string }
	{
		return {
			groupName: Text.getRandom(),
		};
	},
	computed:
	{
		options(): RadioOptionItem[]
		{
			return this.items;
		},
		selectedValue(): OptionValue
		{
			return this.options.find((option) => {
				return option.selected === true;
			});
		},
	},
	methods:
	{
		onInput(option: RadioOptionItem): void
		{
			this.$emit('change', option.value);
		},
	},
	template: `
		<div class="bx-im-content-create-chat-radio__container">
			<label v-for="option in options" class="bx-im-content-create-chat-radio__item ui-ctl ui-ctl-radio">
				<input type="radio" class="ui-ctl-element" :name="groupName" :checked="option.selected" @input="onInput(option)">
				<div class="ui-ctl-label-text">
					<!-- Text -->
					<div v-if="option.html" class="bx-im-content-create-chat-radio__label_title" v-html="option.text"></div>
					<div v-else class="bx-im-content-create-chat-radio__label_title">{{ option.text }}</div>
					<!-- Subtext -->
					<template v-if="option.subtext">
						<div v-if="option.html" class="bx-im-content-create-chat-radio__label_subtitle" v-html="option.subtext"></div>
						<div v-else class="bx-im-content-create-chat-radio__label_subtitle">{{ option.subtext }}</div>
					</template>
				</div>
			</label>
		</div>
	`,
};
