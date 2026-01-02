import { FormSettingsForm } from 'landing.ui.form.formsettingsform';
import { Dom, Runtime, Type } from 'main.core';
import { Button, ButtonColor } from 'ui.buttons';
import { FormSettingsPanel } from 'landing.ui.panel.formsettingspanel';
import { YANDEX_CAPTCHA_SERVICE } from './consts';

import './keys-form.css';

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

					// eslint-disable-next-line promise/catch-or-return
					Runtime
						.loadExtension('crm.form.captcha')
						.then(({ Captcha }) => {
							this.getButton().setWaiting(false);

							return Captcha.open(this.options.type);
						})
						.then((result) => {
							this.value = { ...result };
							const formSettingsPanel = FormSettingsPanel.getInstance();
							if (this.options.type === YANDEX_CAPTCHA_SERVICE)
							{
								formSettingsPanel.getFormDictionary().captcha.yandexCaptcha.hasKeys = (
									Type.isStringFilled(result.key) && Type.isStringFilled(result.secret)
								);
							}
							else
							{
								formSettingsPanel.getFormDictionary().captcha.recaptcha.hasKeys = (
									Type.isStringFilled(result.key) && Type.isStringFilled(result.secret)
								);
							}
							const activeButton = formSettingsPanel.getSidebarButtons().find((button) => {
								return button.isActive();
							});
							if (activeButton)
							{
								activeButton.getLayout().click();
							}

							this.emit('onChange');
						})
					;
				},
			});
		});
	}

	serialize(): { [p: string]: * }
	{
		return this.value;
	}
}
