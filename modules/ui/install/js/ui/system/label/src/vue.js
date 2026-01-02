import { Label as JSLabel, LabelSize, LabelStyle, LabelIcon } from './index';

export const Label = {
	name: 'UILabel',
	props: {
		size: {
			type: String,
			required: false,
			default: LabelSize.MD,
			validator: (value) => {
				return Object.values(LabelSize).includes(value);
			},
		},
		style: {
			type: String,
			required: false,
			default: LabelStyle.FILLED,
			validator: (value) => {
				return Object.values(LabelStyle).includes(value);
			},
		},
		bordered: {
			type: Boolean,
			required: false,
			default: false,
		},
		value: {
			type: String,
			required: true,
		},
		icon: {
			type: String,
			required: false,
			default: '',
			validator: (value) => Object.values(LabelIcon).includes(value),
		},
	},
	watch: {
		value(newValue: string)
		{
			this.label?.setValue(newValue);
		},
		size(newSize: string)
		{
			this.label?.setSize(newSize);
		},
		style(newStyle: string)
		{
			this.label?.setStyle(newStyle);
		},
		bordered(flag: boolean)
		{
			this.label?.setBordered(flag);
		},
		icon(iconName: string)
		{
			this.label?.setIcon(iconName);
		},
	},
	beforeMount(): void
	{
		this.label = new JSLabel({
			size: this.size,
			style: this.style,
			bordered: this.bordered,
			value: this.value,
			icon: this.icon,
		});
	},
	unmount(): void
	{
		this.label.destroy();
		this.label = null;
	},
	mounted()
	{
		this.label.renderOnNode(this.$refs.container);
	},
	template: `
		<div ref="container"></div>
	`,
};
