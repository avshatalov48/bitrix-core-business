// eslint-disable-next-line max-classes-per-file
import { Counter, CounterColor as ButtonCounterColor, CounterSize as ButtonCounterSize, CounterStyle as ButtonCounterStyle } from 'ui.cnt';

export type ButtonCounterOptions = {
	color: ?string;
	style: ?ButtonCounterStyle;
	value: ?number;
	maxValue: ?number;
	useSymbolPercent?: boolean;
}

export class ButtonCounter
{
	#counter: Counter;

	constructor(options: ButtonCounterOptions) {
		this.validateOptions(options);

		this.#counter = new Counter({
			color: options.color ?? ButtonCounterColor.DANGER,
			style: options.style ?? ButtonCounterStyle.FILLED_ALERT,
			size: options.size ?? ButtonCounterSize.MEDIUM,
			value: options.value,
			maxValue: options.maxValue,
			usePercentSymbol: options.useSymbolPercent,
			useAirDesign: true,
		});
	}

	render(): HTMLElement
	{
		return this.#counter.render();
	}

	getValue(): number
	{
		return this.#counter.getValue();
	}

	setValue(value: number): void
	{
		this.#counter.update(value);
	}

	setColor(color: string): void
	{
		this.#counter.setColor(color);
	}

	validateOptions(options: ButtonCounterOptions): void
	{
		// todo add implementation
	}
}
