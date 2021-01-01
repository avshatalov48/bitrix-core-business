import {BaseField} from 'landing.ui.field.basefield';
import {Tag, Dom} from 'main.core';
import {Button} from 'ui.buttons';
import {Loc} from 'landing.loc';

import './css/style.css';

export default class WidgetField extends BaseField
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Filed.WidgetField');
		this.setLayoutClass('landing-ui-filed-widget');

		Dom.append(this.getInputWrapper(), this.layout);
	}

	getInputWrapper(): HTMLDivElement
	{
		return this.cache.remember('inputWrapper', () => {
			return Tag.render`
				<div class="landing-ui-filed-widget-wrapper">
					${this.input}
					${this.getSettingsButton().render()}
				</div>
			`;
		});
	}

	getSettingsButton(): Button
	{
		return this.cache.remember('settingsButton', () => {
			return new Button({
				text: Loc.getMessage('LANDING_FORM_EMBED_WIDGET_BUTTON_LABEL'),
				color: Button.Color.LIGHT_BORDER,
				icon: Button.Icon.SETTING,
			});
		});
	}
}