import { Counter as JsCounter, CounterSize, CounterStyle } from 'ui.cnt';

// @vue/component
export const Counter = {
	name: 'BCounter',
	props: {
		value:
		{
			type: Number,
			default: 0,
		},
		maxValue:
		{
			type: Number,
			default: 99,
		},
		style: {
			type: String,
			required: false,
			default: CounterStyle.FILLED_ALERT,
			validator: (value) => {
				return Object.values(CounterStyle).includes(value);
			},
		},
		size: {
			type: String,
			required: false,
			default: CounterSize.MEDIUM,
			validator: (value) => {
				return Object.values(CounterSize).includes(value);
			},
		},
		border:
		{
			type: Boolean,
			default: false,
		},
		percent:
		{
			type: Boolean,
			default: false,
		},
	},
	watch:
	{
		value(newValue: number)
		{
			this.counter.update(newValue);
		},
		style(newStyle: string)
		{
			this.counter.setStyle(newStyle);
		},
		size(newSize: string)
		{
			this.counter.setSize(newSize);
		},
		maxValue(newMaxValue: number)
		{
			this.counter.setMaxValue(newMaxValue);
		},
		border(useBorder: boolean)
		{
			this.counter.setBorder(useBorder);
		},
	},
	beforeMount()
	{
		this.counter = new JsCounter({
			useAirDesign: true,
			style: this.style,
			size: this.size,
			value: this.value,
			maxValue: this.maxValue,
			usePercentSymbol: this.percent,
			border: this.border,
			node: this.$refs.counter,
		});
	},
	mounted()
	{
		this.counter.renderOnNode(this.$refs.counter);
	},
	unmounted()
	{
		this.counter.destroy();
		this.counter = null;
	},
	template: `
		<div ref="counter"></div>
	`,
};
