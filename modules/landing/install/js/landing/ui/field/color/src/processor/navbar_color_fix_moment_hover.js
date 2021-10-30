import Color from './color';

export default class NavbarColorFixMomentHover extends Color
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.Processor.NavbarColorFixMomentHover');
		this.property = 'color';
		this.variableName = '--navbar-color--fix-moment--hover';
		this.className = 'u-navbar-color--fix-moment--hover';
		this.pseudoClass = ':hover';
	}
}