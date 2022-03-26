import {BaseButton} from "landing.ui.button.basebutton";
import {Dom} from 'main.core';
import {Label, LabelColor, LabelSize} from 'ui.label';

import './css/action_button.css';

/**
 * @memberOf BX.Landing.UI.Button
 */
export class ActionButton extends BaseButton
{
	separate: boolean;

	constructor(id: string, options: {})
	{
		super(id, options);
		this.layout.classList.add("landing-ui-button-action");
		this.separate = Reflect.has(options, 'separate') ? options.separate : false;
		this.label = Reflect.has(options, 'label') ? options.label : null;
		if (this.separate)
		{
			this.layout.classList.add("--separate");
		}
		if (this.label)
		{
			const label = new Label({
				text: this.label,
				color: LabelColor.PRIMARY,
				size: LabelSize.SM,
				fill: true
			});
			Dom.append(label.render(), this.layout);
		}
	}
}