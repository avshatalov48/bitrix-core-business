import './spinner.css';

export const SpinnerSize = Object.freeze({
	XXS: 'XXS',
	S: 'S',
	L: 'L',
});

export const SpinnerColor = Object.freeze({
	grey: 'grey',
	blue: 'blue',
});

// @vue/component
export const Spinner = {
	name: 'MessengerSpinner',
	props: {
		size: {
			type: String,
			default: SpinnerSize.S,
		},
		color: {
			type: String,
			default: SpinnerColor.blue,
		},
	},
	computed:
	{
		sizeClassName(): string
		{
			return `--size-${this.size.toLowerCase()}`;
		},
		colorClassName(): string
		{
			return `--color-${this.color.toLowerCase()}`;
		},
	},
	template: `
		<div class="bx-im-elements-spinner__container bx-im-elements-spinner__scope">
			<div class="bx-im-elements-spinner__spinner" :class="[sizeClassName, colorClassName]"></div>
		</div>
	`,
};
