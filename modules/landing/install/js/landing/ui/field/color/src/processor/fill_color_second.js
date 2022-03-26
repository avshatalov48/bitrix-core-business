import Color from "./color";

export default class FillColorSecond extends Color
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.Processor.FillColorSecond');
		this.property = 'fill';
		this.pseudoClass = ':after';
		this.variableName = '--fill-second';
		this.className = 'g-fill-second';
	}
}