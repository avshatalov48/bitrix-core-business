import Color from './color';

export default class NavbarCollapseBgColor extends Color
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.Processor.NavbarCollapseBgColor');
		this.property = 'background-color';
		this.variableName = '--navbar-collapse-bg-color';
		this.className = 'u-navbar-collapse-bg';
	}
}