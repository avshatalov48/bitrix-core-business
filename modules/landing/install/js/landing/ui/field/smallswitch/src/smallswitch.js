import {Switch} from 'landing.ui.field.switch';
import {Dom} from 'main.core';

import './css/style.css';
import {Loc} from 'landing.loc';

/**
 * @memberOf BX.Landing.UI.Field
 */
export class SmallSwitch extends Switch
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.SmallSwitch');
		Dom.addClass(this.layout, 'landing-ui-field-small-switch');
		Dom.attr(this.slider, 'data-text', Loc.getMessage('LANDING_SMALL_SWITCHER_ON'));
		this.adjustLabel();
	}

	adjustLabel()
	{
		if (this.getValue())
		{
			Dom.attr(this.slider, 'data-text', Loc.getMessage('LANDING_SMALL_SWITCHER_ON'));
		}
		else
		{
			Dom.attr(this.slider, 'data-text', Loc.getMessage('LANDING_SMALL_SWITCHER_OFF'));
		}
	}

	onChange()
	{
		super.onChange();
		this.adjustLabel();
	}
}