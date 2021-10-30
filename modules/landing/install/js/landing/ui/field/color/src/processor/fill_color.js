import Color from "./color";

export default class FillColor extends Color
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.Processor.FillColor');
		this.property = 'fill';
		this.variableName = '--fill-first';
		this.className = 'g-fill-first';
	}
}