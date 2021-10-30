import Color from "./color";

export default class BorderColorTop extends Color
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.Processor.BorderColorTop');
		this.property = 'border-top-color';
		this.variableName = '--border-color-top';
		this.className = 'g-border-color-top';
	}
}