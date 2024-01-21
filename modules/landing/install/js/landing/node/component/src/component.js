import { Base } from 'landing.node.base';

export class Component extends Base
{
	constructor(options)
	{
		super(options);
		this.type = 'component';
		this.value = '';
	}

	/**
	 * @inheritDoc
	 * @return {BX.Landing.UI.Field.BaseField}
	 */
	getField(): BX.Landing.UI.Field.BaseField
	{
		return new BX.Landing.UI.Field.BaseField({
			selector: this.selector,
		});
	}

	/**
	 * Gets value
	 * @return {string}
	 */
	getValue(): string
	{
		return this.value;
	}

	/**
	 * Sets value
	 * @inheritDoc
	 */
	setValue(value, preventSave, preventHistory)
	{
		this.value = value;
	}
}

BX.Landing.Node.Component = Component;
