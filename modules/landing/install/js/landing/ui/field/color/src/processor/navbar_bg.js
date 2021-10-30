import Color from './color';

export default class NavbarBgColor extends Color
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.Processor.NavbarBgColor');
		this.property = 'background-color';
		this.variableName = '--navbar-bg-color';
		this.className = 'u-navbar-bg';
	}
}