import {FormSettingsForm} from 'landing.ui.form.formsettingsform';

import './keys-form.css';
import {Dom, Runtime, Type} from 'main.core';
import {Button, ButtonColor} from 'ui.buttons';
import {FormSettingsPanel} from 'landing.ui.panel.formsettingspanel';

export default class KeysForm extends FormSettingsForm
{
	constructor(options: {[key: string]: any})
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.Content.SpamProtection.KeysForm');
		Dom.addClass(this.layout, 'landing-ui-form-form-keys-settings');

		this.getButton().renderTo(this.layout);

		this.value = {};
	}

	getButton(): Button
	{
		return this.cache.remember('button', () => {
			return new Button({
				text: this.options.buttonLabel,
				color: ButtonColor.LIGHT_BORDER,
				onclick: () => {
					this.getButton().setWaiting(true);

					Runtime
						.loadExtension('crm.form.captcha')
						.then(({Captcha}) => {
							this.getButton().setWaiting(false);
							return Captcha.open();
						})
						.then((result) => {
							this.value = {...result};
							const formSettingsPanel = FormSettingsPanel.getInstance();
							formSettingsPanel.getFormDictionary().captcha.hasKeys = (
								Type.isStringFilled(result.key) && Type.isStringFilled(result.secret)
							);
							const activeButton = formSettingsPanel.getSidebarButtons().find((button) => {
								return button.isActive();
							});
							if (activeButton)
							{
								activeButton.getLayout().click();
							}

							this.emit('onChange');
						});
				},
			});
		});
	}

	serialize(): { [p: string]: * }
	{
		return this.value;
	}
}