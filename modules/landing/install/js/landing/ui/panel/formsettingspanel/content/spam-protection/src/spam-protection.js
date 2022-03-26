import {HeaderCard} from 'landing.ui.card.headercard';
import {Loc} from 'landing.loc';
import {FormSettingsForm} from 'landing.ui.form.formsettingsform';
import {RadioButtonField} from 'landing.ui.field.radiobuttonfield';
import {ContentWrapper} from 'landing.ui.panel.basepresetpanel';
import {Dom, Text} from 'main.core';
import KeysForm from './internal/keys-form';
import {MessageCard} from 'landing.ui.card.messagecard';

import './css/style.css';

export default class SpamProtection extends ContentWrapper
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.SpamProtection');

		const header = new HeaderCard({
			title: Loc.getMessage('LANDING_SPAM_PROTECTION_TITLE'),
		});

		const message = new MessageCard({
			header: Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_MESSAGE_TITLE'),
			description: Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_MESSAGE_TEXT'),
			angle: false,
		});

		const captchaTypeForm = new FormSettingsForm({
			id: 'type',
			description: null,
			fields: [
				new RadioButtonField({
					selector: 'use',
					title: Loc.getMessage('LANDING_SPAM_PROTECTION_TABS_TITLE'),
					value: Text.toBoolean(this.options.formOptions.data.recaptcha.use) ? 'hidden' : 'disabled',
					items: [
						{
							id: 'disabled',
							title: Loc.getMessage('LANDING_SPAM_PROTECTION_TAB_DISABLED'),
							icon: 'landing-ui-spam-protection-icon-disabled',
						},
						{
							id: 'hidden',
							title: Loc.getMessage('LANDING_SPAM_PROTECTION_TAB_HIDDEN'),
							icon: 'landing-ui-spam-protection-icon-hidden',
						},
					],
				}),
			],
		});

		this.addItem(header);
		this.addItem(message);
		this.addItem(captchaTypeForm);

		captchaTypeForm.subscribe('onChange', this.onTypeChange.bind(this));
		this.onTypeChange();
	}

	hasDefaultsCaptchaKeys(): boolean
	{
		return Text.toBoolean(this.options.formOptions.captcha.hasDefaults);
	}

	hasCustomKeys(): boolean
	{
		return Text.toBoolean(this.options.dictionary.captcha.hasKeys);
	}

	onTypeChange()
	{
		Dom.remove(this.getCustomKeysForm().getLayout());
		Dom.remove(this.getRequiredKeysForm().getLayout());
		Dom.remove(this.getKeysSettingsForm().getLayout());

		if (this.getValue().recaptcha.use)
		{
			if (!this.hasDefaultsCaptchaKeys() && !this.hasCustomKeys())
			{
				this.addItem(this.getRequiredKeysForm());
			}

			if (
				(!this.hasDefaultsCaptchaKeys() && this.hasCustomKeys())
				|| (this.hasDefaultsCaptchaKeys() && this.hasCustomKeys())
			)
			{
				this.addItem(this.getKeysSettingsForm());
			}

			if (this.hasDefaultsCaptchaKeys() && !this.hasCustomKeys())
			{
				this.addItem(this.getCustomKeysForm());
			}
		}
	}

	getCustomKeysForm(): KeysForm
	{
		return this.cache.remember('customKeysForm', () => {
			return new KeysForm({
				title: Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_TITLE'),
				buttonLabel: Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_CUSTOM_BUTTON_LABEL'),
			});
		});
	}

	getRequiredKeysForm()
	{
		return this.cache.remember('requiredKeysForm', () => {
			return new KeysForm({
				title: Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_TITLE'),
				buttonLabel: Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_BUTTON_LABEL'),
				description: Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_REQUIRED_DESCRIPTION'),
			});
		});
	}

	getKeysSettingsForm(): KeysForm
	{
		return this.cache.remember('keysSettingsForm', () => {
			return new KeysForm({
				title: Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_TITLE'),
				buttonLabel: Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_CHANGE_BUTTON_LABEL'),
			});
		});
	}

	// eslint-disable-next-line class-methods-use-this
	valueReducer(sourceValue: {[p: string]: any}): {[p: string]: any}
	{
		return {
			recaptcha: {
				use: sourceValue.use === 'hidden',
				...this.getKeysSettingsForm().serialize(),
				...this.getCustomKeysForm().serialize(),
				...this.getRequiredKeysForm().serialize(),
			},
		};
	}

	onChange(event: BaseEvent)
	{
		this.emit('onChange', {...event.getData(), skipPrepare: true});
	}
}