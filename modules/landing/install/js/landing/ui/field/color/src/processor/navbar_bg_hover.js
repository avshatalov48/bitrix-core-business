import Color from './color';

export default class NavbarBgColorHover extends Color
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.Processor.NavbarBgColorHover');
		this.property = 'background-color';
		this.variableName = '--navbar-bg-color--hover';
		this.className = 'u-navbar-bg--hover';
		this.pseudoClass = ':hover';
	}
}