import { Type } from 'main.core';

import './css/button.css';

import { ButtonColor, ButtonSize, type CustomColorScheme } from './const/const';

// @vue/component
export const ChatButton = {
	name: 'ChatButton',
	props:
	{
		size: {
			type: String,
			required: true,
		},
		text: {
			type: String,
			required: false,
			default: '',
		},
		icon: {
			type: String,
			required: false,
			default: '',
		},
		color: {
			type: String,
			required: false,
			default: ButtonColor.Primary,
		},
		customColorScheme: {
			type: Object,
			required: false,
			default: (): CustomColorScheme => {
				return {
					borderColor: '',
					backgroundColor: '',
					iconColor: '',
					textColor: '',
					hoverColor: '',
					textHoverColor: '',
				};
			},
		},
		isRounded: {
			type: Boolean,
			required: false,
			default: false,
		},
		isDisabled: {
			type: Boolean,
			required: false,
			default: false,
		},
		isLoading: {
			type: Boolean,
			required: false,
			default: false,
		},
		isUppercase: {
			type: Boolean,
			required: false,
			default: true,
		},
	},
	emits: ['click'],
	computed:
	{
		buttonStyles()
		{
			const result = {};
			if (this.hasCustomColorScheme)
			{
				result['--im-button__border-color'] = this.customColorScheme.borderColor;
				result['--im-button__background-color'] = this.customColorScheme.backgroundColor;
				result['--im-button__color'] = this.customColorScheme.textColor;
				result['--im-button__background-color_hover'] = this.customColorScheme.hoverColor;
				result['--im-button__color_hover'] = this.customColorScheme.textHoverColor ?? this.customColorScheme.textColor;
			}

			return result;
		},
		buttonClasses(): string[]
		{
			const classes = [`--size-${this.size.toLowerCase()}`];
			if (!this.hasCustomColorScheme)
			{
				classes.push(`--color-${this.color.toLowerCase()}`);
			}

			if (this.isRounded)
			{
				classes.push('--rounded');
			}

			if (this.isDisabled)
			{
				classes.push('--disabled');
			}

			if (this.isLoading)
			{
				classes.push('--loading');
			}

			if (this.isUppercase && this.size !== ButtonSize.S)
			{
				classes.push('--uppercase');
			}

			if (this.text === '')
			{
				classes.push('--no-text');
			}

			return classes;
		},
		iconStyles()
		{
			const result = {};
			if (this.hasCustomColorScheme)
			{
				result.backgroundColor = this.customColorScheme.iconColor;
			}

			return result;
		},
		iconClasses(): string[]
		{
			const classes = [`--${this.icon}`];
			if (this.hasCustomColorScheme)
			{
				classes.push('--custom-color');
			}

			return classes;
		},
		hasCustomColorScheme(): boolean
		{
			return Type.isPlainObject(this.customColorScheme)
				&& Type.isStringFilled(this.customColorScheme.borderColor)
				&& Type.isStringFilled(this.customColorScheme.iconColor)
				&& Type.isStringFilled(this.customColorScheme.textColor)
				&& Type.isStringFilled(this.customColorScheme.hoverColor);
		},
	},
	methods:
	{
		onClick(event: PointerEvent)
		{
			if (this.isDisabled || this.isLoading)
			{
				return;
			}

			this.$emit('click', event);
		},
	},
	template:
	`
		<button
			:class="buttonClasses"
			:style="buttonStyles"
			@click.stop="onClick"
			class="bx-im-button__scope bx-im-button__container"
		>
			<span v-if="icon" :style="iconStyles" :class="iconClasses" class="bx-im-button__icon"></span>
			<span class="bx-im-button__text">{{ text }}</span>
		</button>
	`,
};
