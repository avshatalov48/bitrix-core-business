import Color from "./color";

export default class BorderColorHover extends Color
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.Processor.BorderColorHover');
		this.property = 'border-color';
		this.variableName = '--border-color--hover';
		this.className = 'g-border-color--hover';
		this.pseudoClass = ':hover';
	}
}