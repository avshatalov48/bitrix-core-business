export const FloatButtonColor = Object.freeze({
	accent: 'accent',
	alert: 'alert',
	success: 'success',
});

export const FloatButtonIcon = Object.freeze({
	chevronDown: 'chevron-down',
	heart: 'heart',
	atSymbol: 'at-symbol',
	comment: 'comment',
});

// @vue/component
export const FloatButton = {
	name: 'FloatButton',
	props:
	{
		counter: {
			type: Number,
			default: 0,
		},
		color: {
			type: String,
			required: false,
			default: FloatButtonColor.accent,
			validator: (value) => {
				return Object.values(FloatButtonColor).includes(value);
			},
		},
		icon: {
			type: String,
			required: true,
			validator: (value) => {
				return Object.values(FloatButtonIcon).includes(value);
			},
		},
	},
	computed:
	{
		buttonClassname(): string[]
		{
			return [
				'bx-im-dialog-chat__float-button',
				`--color-${this.color}`,
				`--icon-${this.icon}`,
			];
		},
		formattedCounter(): string
		{
			if (this.counter === 0)
			{
				return '';
			}

			if (this.counter > 99)
			{
				return '99+';
			}

			return String(this.counter);
		},
	},
	template: `
		<div :class="buttonClassname">
			<div class="bx-im-dialog-chat__float-button_icon"></div>
			<div v-if="counter" class="bx-im-dialog-chat__float-button_counter">
				{{ formattedCounter }}
			</div>
		</div>
	`,
};
