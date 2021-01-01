import {BaseForm} from 'landing.ui.form.baseform';
import {Dom} from 'main.core';

import './css/balloon_form.css';

/**
 * @memberOf BX.Landing.UI.Form
 */
export class BalloonForm extends BaseForm
{
	constructor(options: {[key: string]: any} = {})
	{
		super(options);
		Dom.addClass(this.layout, 'landing-ui-form-balloon');
	}
}