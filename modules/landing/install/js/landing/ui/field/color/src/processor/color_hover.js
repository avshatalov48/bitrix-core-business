import Color from './color';

export default class ColorHover extends Color
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.Processor.ColorHover');
		this.property = 'color';
		this.variableName = '--color-hover';
		this.className = 'g-color--hover';
		this.pseudoClass = ':hover';
	}
}