import BgColor from './bg_color';

export default class BgColorHover extends BgColor
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.Processor.BgColorHover');
		this.property = ['background-image', 'background-color'];
		this.variableName = '--bg-hover';
		this.className = 'g-bg--hover';
		this.pseudoClass = ':hover';
	}
}