import Color from './color';

export default class NavbarColorFixMoment extends Color
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.Processor.NavbarColorFixMoment');
		this.property = 'color';
		this.variableName = '--navbar-color--fix-moment';
		this.className = 'u-navbar-color--fix-moment';
	}
}