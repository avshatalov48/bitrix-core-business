import Color from "./color";

export default class BorderColor extends Color
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.Processor.BorderColor');
		this.property = 'border-color';
		this.variableName = '--border-color';
		this.className = 'g-border-color';
	}
}