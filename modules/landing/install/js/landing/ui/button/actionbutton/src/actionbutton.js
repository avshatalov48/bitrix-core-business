import { BaseButton } from 'landing.ui.button.basebutton';
import { Dom, Tag } from 'main.core';
import { Label, LabelColor, LabelSize } from 'ui.label';

import 'ui.fonts.opensans';
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
		this.layout.classList.add('landing-ui-button-action');
		this.separate = Reflect.has(options, 'separate') ? options.separate : false;
		this.label = Reflect.has(options, 'label') ? options.label : null;
		this.disabled = Reflect.has(options, 'disabled') ? options.disabled : false;
		this.disabledHint = Reflect.has(options, 'disabledHint') ? options.disabledHint : null;
		if (this.separate)
		{
			this.layout.classList.add('--separate');
		}

		if (this.label)
		{
			const label = new Label({
				text: this.label,
				color: LabelColor.PRIMARY,
				size: LabelSize.SM,
				fill: true,
			});
			Dom.append(label.render(), this.layout);
		}

		if (this.disabled && this.disabledHint)
		{
			this.layout = Tag.render`
				<div class="landing-ui-button-action-container">${this.layout}</div>
			`;
			this.layout.setAttribute('data-hint', this.disabledHint);
			this.layout.setAttribute('data-hint-no-icon', '');
			BX.UI.Hint.initNode(this.layout);
		}
	}
}