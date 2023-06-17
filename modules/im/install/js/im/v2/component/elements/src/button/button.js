import {Type} from 'main.core';

import './button.css';

export const ButtonSize = {
	S: 'S',
	M: 'M',
	L: 'L',
	XL: 'XL',
	XXL: 'XXL'
};

export const ButtonColor = {
	Primary: 'primary',
	PrimaryLight: 'primary-light',
	Success: 'success',
	Danger: 'danger',
	LightBorder: 'light-border',
	DangerBorder: 'danger-border',
	PrimaryBorder: 'primary-border',
	Link: 'link'
};

export const ButtonIcon = {
	Plus: 'plus',
	Link: 'link',
	Call: 'call',
	EndCall: 'end-call',
	AddUser: 'add-user',
};

export type CustomColorScheme = {
	borderColor: string,
	backgroundColor: string,
	iconColor: string,
	textColor: string,
	hoverColor: string
};

// @vue/component
export const Button = {
	name: 'MessengerButton',
	props:
	{
		size: {
			type: String,
			required: true
		},
		text: {
			type: String,
			required: false,
			default: ''
		},
		icon: {
			type: String,
			required: false,
			default: ''
		},
		color: {
			type: String,
			required: false,
			default: ButtonColor.Primary
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
					hoverColor: ''
				};
			}
		},
		isRounded: {
			type: Boolean,
			required: false,
			default: false
		},
		isDisabled: {
			type: Boolean,
			required: false,
			default: false
		},
		isLoading: {
			type: Boolean,
			required: false,
			default: false
		},
		isUppercase: {
			type: Boolean,
			required: false,
			default: true
		}
	},
	emits: ['click'],
	computed:
	{
		buttonStyles()
		{
			const result = {};
			if (this.hasCustomColorScheme)
			{
				result['borderColor'] = this.customColorScheme.borderColor;
				result['backgroundColor'] = this.customColorScheme.backgroundColor;
				result['color'] = this.customColorScheme.textColor;
				result['--im-button__background-color_hover'] = this.customColorScheme.hoverColor;
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
				result['backgroundColor'] = this.customColorScheme.iconColor;
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
			return Type.isStringFilled(this.customColorScheme.borderColor)
				&& Type.isStringFilled(this.customColorScheme.iconColor)
				&& Type.isStringFilled(this.customColorScheme.textColor)
				&& Type.isStringFilled(this.customColorScheme.hoverColor);
		}
	},
	methods: {
		onClick(event: PointerEvent)
		{
			if (this.isDisabled || this.isLoading)
			{
				return;
			}

			this.$emit('click', event);
		}
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
	`
};