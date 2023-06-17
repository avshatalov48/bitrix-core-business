import './spinner.css';

export const SpinnerSize = Object.freeze({
	S: 'S',
	L: 'L',
});

// @vue/component
export const Spinner = {
	name: 'MessengerSpinner',
	props: {
		size: {
			type: String,
			default: SpinnerSize.S
		},
	},
	computed:
	{
		sizeClassName(): string
		{
			return `--size-${this.size.toLowerCase()}`;
		}
	},
	template: `
		<div class="bx-im-elements-spinner__container">
			<div class="bx-im-elements-spinner__spinner" :class="sizeClassName"></div>
		</div>
	`
};