import Color from './color';

export default class NavbarColor extends Color
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.Processor.NavbarColor');
		this.property = 'color';
		this.variableName = '--navbar-color';
		this.className = 'u-navbar-color';
	}
}