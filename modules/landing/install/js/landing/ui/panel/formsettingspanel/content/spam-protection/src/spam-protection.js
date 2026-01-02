import { HeaderCard } from 'landing.ui.card.headercard';
import { Loc } from 'landing.loc';
import { FormSettingsForm } from 'landing.ui.form.formsettingsform';
import { RadioButtonField } from 'landing.ui.field.radiobuttonfield';
import { ContentWrapper } from 'landing.ui.panel.basepresetpanel';
import { Dom, Text } from 'main.core';
import KeysForm from './internal/keys-form';
import { MessageCard } from 'landing.ui.card.messagecard';
import { YANDEX_AVAILABLE_ZONES, YANDEX_CAPTCHA_SERVICE, GOOGLE_CAPTCHA_SERVICE } from './internal/consts';

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

		if (YANDEX_AVAILABLE_ZONES.has(options.dictionary.region))
		{
			// yandexCaptcha is chosen for ru region or if chosen explicitly
			let chosenCaptcha = GOOGLE_CAPTCHA_SERVICE;
			if (this.options.formOptions.captcha.service === GOOGLE_CAPTCHA_SERVICE
				|| (this.options.formOptions.captcha.service === '' && this.options.formOptions.captcha.recaptcha.use === true)
			)
			{
				chosenCaptcha = GOOGLE_CAPTCHA_SERVICE;
			}
			else if (options.dictionary.region === 'ru' || this.options.formOptions.captcha.service === YANDEX_CAPTCHA_SERVICE)
			{
				chosenCaptcha = YANDEX_CAPTCHA_SERVICE;
			}

			const captchaServiceForm = this.getServiceForm(false, chosenCaptcha);

			this.addItem(header);
			this.addItem(message);
			this.addItem(captchaServiceForm);

			captchaServiceForm.subscribe('onChange', this.onCaptchaServiceChange.bind(this));
			this.onCaptchaServiceChange();

			return;
		}

		const captchaServiceForm = this.getServiceForm(true, GOOGLE_CAPTCHA_SERVICE);
		const recaptchaTypeForm = this.getRecaptchaForm();
		this.addItem(captchaServiceForm);
		this.addItem(recaptchaTypeForm);
		recaptchaTypeForm.subscribe('onChange', this.onRecaptchaTypeChange.bind(this));
		this.onRecaptchaTypeChange();
	}

	getServiceForm(isHidden: boolean, chosenCaptcha: string): FormSettingsForm
	{
		return new FormSettingsForm({
			id: 'service',
			description: null,
			hidden: isHidden,
			fields: [
				new BX.Landing.UI.Field.Dropdown({
					selector: 'service',
					title: Loc.getMessage('LANDING_SPAM_PROTECTION_SERVICE_TITLE'),
					items: [
						{
							name: Loc.getMessage('LANDING_SPAM_PROTECTION_SERVICE_OPTION_YANDEX'),
							value: YANDEX_CAPTCHA_SERVICE,
						},
						{
							name: Loc.getMessage('LANDING_SPAM_PROTECTION_SERVICE_OPTION_GOOGLE'),
							value: GOOGLE_CAPTCHA_SERVICE,
						},
					],
					content: chosenCaptcha,
				}),
			],
		});
	}

	onCaptchaServiceChange(): void
	{
		const recaptchaTypeForm = this.getRecaptchaForm();
		const yandexCaptchaForm = this.getYandexCaptchaForm();
		const foreignServiceWarningForm = this.getForeignServiceWarningForm();
		Dom.remove(recaptchaTypeForm.getLayout());
		Dom.remove(yandexCaptchaForm.getLayout());
		Dom.remove(foreignServiceWarningForm.getLayout());

		if (this.getValue().captcha.service === GOOGLE_CAPTCHA_SERVICE)
		{
			yandexCaptchaForm.unsubscribe('onChange', this.onYandexTypeChange.bind(this));
			this.onYandexTypeChange();

			this.addForeignServiceWarningForm(foreignServiceWarningForm, GOOGLE_CAPTCHA_SERVICE);
			this.addItem(recaptchaTypeForm);
			recaptchaTypeForm.subscribe('onChange', this.onRecaptchaTypeChange.bind(this));
			this.onRecaptchaTypeChange();
		}
		else
		{
			recaptchaTypeForm.unsubscribe('onChange', this.onRecaptchaTypeChange.bind(this));
			this.onRecaptchaTypeChange();

			this.addForeignServiceWarningForm(foreignServiceWarningForm, YANDEX_CAPTCHA_SERVICE);
			this.addItem(yandexCaptchaForm);
			yandexCaptchaForm.subscribe('onChange', this.onYandexTypeChange.bind(this));
			this.onYandexTypeChange();
		}
	}

	addForeignServiceWarningForm(form: FormSettingsForm, service: string): void
	{
		if (this.options.dictionary.region === 'by'
			|| (this.options.dictionary.region === 'ru' && service === GOOGLE_CAPTCHA_SERVICE)
		)
		{
			this.addItem(form);
		}
	}

	getForeignServiceWarningForm(): FormSettingsForm
	{
		return this.cache.remember('foreignServiceWarning', () => {
			return new FormSettingsForm({
				id: 'foreignServiceWarning',
				description: null,
				fields: [
					new MessageCard({
						selector: 'warningForeign',
						context: 'warning',
						description: Loc.getMessage('LANDING_SPAM_PROTECTION_MESSAGE_WARNING_FOREIGN'),
						angle: false,
						closeable: false,
					}),
				],
			});
		});
	}

	getRecaptchaForm(): FormSettingsForm
	{
		return this.cache.remember('recaptchaForm', () => {
			return new FormSettingsForm({
				id: 'type',
				description: null,
				fields: [
					new MessageCard({
						selector: 'warningCaptcha',
						context: 'warning',
						description: Loc.getMessage('LANDING_SPAM_PROTECTION_MESSAGE_WARNING_RECAPTCHA')
							.replace('#URL_POLICIES_PRIVACY#', 'https://policies.google.com/privacy')
							.replace('#URL_POLICIES_TERMS#', 'https://policies.google.com/terms'),
						angle: false,
						closeable: false,
					}),
					new RadioButtonField({
						selector: 'recaptchaUse',
						title: Loc.getMessage('LANDING_SPAM_PROTECTION_TABS_TITLE'),
						value: Text.toBoolean(this.options.formOptions.captcha.recaptcha.use) ? 'hidden' : 'disabled',
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
		});
	}

	getYandexCaptchaForm(): FormSettingsForm
	{
		return this.cache.remember('yandexCaptchaForm', () => {
			return new FormSettingsForm({
				id: 'yandexType',
				description: null,
				fields: [
					new RadioButtonField({
						selector: 'yandexUse',
						title: Loc.getMessage('LANDING_SPAM_PROTECTION_TABS_TITLE_YANDEX'),
						value: Text.toBoolean(this.options.formOptions.captcha.yandexCaptcha.use) ? 'hidden' : 'disabled',
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
		});
	}

	hasDefaultYandexKeys(): boolean
	{
		return Text.toBoolean(this.options.formOptions.captcha.yandexCaptcha.hasDefaults);
	}

	hasCustomYandexKeys(): boolean
	{
		return Text.toBoolean(this.options.dictionary.captcha.yandexCaptcha.hasKeys);
	}

	onYandexTypeChange(): void
	{
		Dom.remove(this.getYandexKeysSettingsForm().getLayout());
		Dom.remove(this.getYandexRequiredKeysForm().getLayout());

		if (this.getValue().captcha.service === YANDEX_CAPTCHA_SERVICE && this.getValue().captcha.yandexCaptcha.use)
		{
			if (!this.hasDefaultYandexKeys() && !this.hasCustomYandexKeys())
			{
				this.addItem(this.getYandexRequiredKeysForm());
			}
			else if (this.hasCustomYandexKeys())
			{
				this.addItem(this.getYandexKeysSettingsForm());
			}
			else if (this.hasDefaultYandexKeys() && !this.hasCustomYandexKeys())
			{
				this.addItem(this.getYandexCustomKeysForm());
			}
		}
	}

	getYandexCustomKeysForm(): KeysForm
	{
		return this.cache.remember('yandexCustomKeysForm', () => {
			return new KeysForm({
				title: Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_YANDEX_KEYS_FORM_TITLE'),
				buttonLabel: Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_CUSTOM_BUTTON_LABEL'),
				type: YANDEX_CAPTCHA_SERVICE,
			});
		});
	}

	getYandexRequiredKeysForm(): KeysForm
	{
		return this.cache.remember('yandexRequiredKeysForm', () => {
			return new KeysForm({
				title: Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_YANDEX_KEYS_FORM_TITLE'),
				buttonLabel: Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_BUTTON_LABEL'),
				description: Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_YANDEX_KEYS_FORM_REQUIRED_DESCRIPTION'),
				type: YANDEX_CAPTCHA_SERVICE,
			});
		});
	}

	getYandexKeysSettingsForm(): KeysForm
	{
		return this.cache.remember('yandexCustomKeysForm', () => {
			return new KeysForm({
				title: Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_YANDEX_KEYS_FORM_TITLE'),
				buttonLabel: Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_CHANGE_BUTTON_LABEL'),
				type: YANDEX_CAPTCHA_SERVICE,
			});
		});
	}

	hasDefaultRecaptchaKeys(): boolean
	{
		return Text.toBoolean(this.options.formOptions.captcha.recaptcha.hasDefaults);
	}

	hasRecaptchaCustomKeys(): boolean
	{
		return Text.toBoolean(this.options.dictionary.captcha.recaptcha.hasKeys);
	}

	onRecaptchaTypeChange(): void
	{
		Dom.remove(this.getRecaptchaCustomKeysForm().getLayout());
		Dom.remove(this.getRecaptchaRequiredKeysForm().getLayout());
		Dom.remove(this.getRecaptchaKeysSettingsForm().getLayout());

		if ((this.getValue().captcha.service === GOOGLE_CAPTCHA_SERVICE
				|| !this.getValue().captcha.service)
			&& this.getValue().captcha.recaptcha.use
		)
		{
			if (!this.hasDefaultRecaptchaKeys() && !this.hasRecaptchaCustomKeys())
			{
				this.addItem(this.getRecaptchaRequiredKeysForm());
			}
			else if (this.hasRecaptchaCustomKeys())
			{
				this.addItem(this.getRecaptchaKeysSettingsForm());
			}
			else if (this.hasDefaultRecaptchaKeys() && !this.hasRecaptchaCustomKeys())
			{
				this.addItem(this.getRecaptchaCustomKeysForm());
			}
		}
	}

	getRecaptchaCustomKeysForm(): KeysForm
	{
		return this.cache.remember('customKeysForm', () => {
			return new KeysForm({
				title: Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_TITLE'),
				buttonLabel: Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_CUSTOM_BUTTON_LABEL'),
				type: GOOGLE_CAPTCHA_SERVICE,
			});
		});
	}

	getRecaptchaRequiredKeysForm(): KeysForm
	{
		return this.cache.remember('requiredKeysForm', () => {
			return new KeysForm({
				title: Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_TITLE'),
				buttonLabel: Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_BUTTON_LABEL'),
				description: Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_REQUIRED_DESCRIPTION'),
				type: GOOGLE_CAPTCHA_SERVICE,
			});
		});
	}

	getRecaptchaKeysSettingsForm(): KeysForm
	{
		return this.cache.remember('keysSettingsForm', () => {
			return new KeysForm({
				title: Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_TITLE'),
				buttonLabel: Loc.getMessage('LANDING_FORM_EDITOR_FORM_CAPTCHA_KEYS_FORM_CHANGE_BUTTON_LABEL'),
				type: GOOGLE_CAPTCHA_SERVICE,
			});
		});
	}

	// eslint-disable-next-line class-methods-use-this
	valueReducer(sourceValue: {[p: string]: any}): {[p: string]: any}
	{
		return {
			captcha: {
				service: sourceValue.service,
				recaptcha: {
					use: sourceValue.recaptchaUse ? sourceValue.recaptchaUse === 'hidden' : this.options.formOptions.captcha.recaptcha.use,
					...this.getRecaptchaKeysSettingsForm().serialize(),
					...this.getRecaptchaCustomKeysForm().serialize(),
					...this.getRecaptchaRequiredKeysForm().serialize(),
				},
				yandexCaptcha: {
					use: sourceValue.yandexUse ? sourceValue.yandexUse === 'hidden' : this.options.formOptions.captcha.yandexCaptcha.use,
					...this.getYandexRequiredKeysForm().serialize(),
					...this.getYandexKeysSettingsForm().serialize(),
				},
			},
		};
	}

	onChange(event: BaseEvent): void
	{
		this.emit('onChange', { ...event.getData(), skipPrepare: true });
	}
}
