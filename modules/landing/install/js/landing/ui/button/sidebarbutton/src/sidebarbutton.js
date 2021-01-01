import {Dom, Tag} from 'main.core';
import {BaseButton} from 'landing.ui.button.basebutton';
import {Loc} from 'landing.loc';

import './css/sidebar_button.css';

/**
 * @memberOf BX.Landing.UI.Button
 */
export class SidebarButton extends BaseButton
{
	constructor(id, options)
	{
		super(id, options);

		Dom.addClass(this.layout, 'landing-ui-button-sidebar');

		if (this.options.child === true)
		{
			Dom.addClass(this.layout, 'landing-ui-button-sidebar-child');
		}

		if (this.options.empty === true)
		{
			Dom.addClass(this.layout, 'landing-ui-button-sidebar-empty');
		}

		if (this.options.important === true && this.options.child === true)
		{
			const label = this.createLabel(
				'landing-ui-button-sidebar-icon-important',
				Loc.getMessage('LANDING_SIDEBAR_BUTTON_IMPORTANT_LABEL_TITLE'),
			);

			Dom.append(label, this.layout);
		}
	}

	createLabel(icon: string, text: string): HTMLSpanElement
	{
		return Tag.render`
			<span class="landing-ui-button-sidebar-icon ${icon}">${text}</span>
		`;
	}
}