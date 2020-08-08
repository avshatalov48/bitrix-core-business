import {FieldViewControllerAbstract} from "./fieldviewcontrollerabstract";
export class FieldViewControllerPreview extends FieldViewControllerAbstract
{
	constructor(params)
	{
		super(params);
	}

	build()
	{
		super.build();
		this.DOM.outerWrap.className = 'calendar-resbook-webform-wrapper calendar-resbook-webform-wrapper-preview calendar-resbook-webform-wrapper-dark';
	}
}