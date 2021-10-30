import Color from './color';

export default class NavbarColorHover extends Color
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.Processor.NavbarColorHover');
		this.property = 'color';
		this.variableName = '--navbar-color--hover';
		this.className = 'u-navbar-color--hover';
		this.pseudoClass = ':hover';
	}
}