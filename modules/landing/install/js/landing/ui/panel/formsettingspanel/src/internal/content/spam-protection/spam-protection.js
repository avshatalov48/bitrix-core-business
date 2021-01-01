import {HeaderCard} from 'landing.ui.card.headercard';
import {Loc} from 'landing.loc';
import {FormSettingsForm} from 'landing.ui.form.formsettingsform';
import {TextField} from 'landing.ui.field.textfield';
import {RadioButtonField} from 'landing.ui.field.radiobuttonfield';
import {ContentWrapper} from 'landing.ui.panel.basepresetpanel';

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

		const captchaTypeForm = new FormSettingsForm({
			id: 'type',
			description: null,
			fields: [
				new RadioButtonField({
					selector: 'use',
					title: Loc.getMessage('LANDING_SPAM_PROTECTION_TABS_TITLE'),
					value: this.options.values.use ? 'hidden' : 'disabled',
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
		this.addItem(captchaTypeForm);
		this.addItem(this.getKeysCheckbox());
		this.addItem(this.getKeysForm());

		const hasKeys = this.options.values.key && this.options.values.secret;

		if (captchaTypeForm.fields[0].getValue() === 'disabled')
		{
			this.hideKeysCheckbox();
			this.hideKeysForm();
		}
		else
		{
			this.showKeysCheckbox();
			this.getKeysCheckbox().fields[0].setValue([hasKeys ? 'useCustomKeys' : '']);
			if (hasKeys)
			{
				this.showKeysForm();
			}
			else
			{
				this.hideKeysForm();
			}
		}

		captchaTypeForm.subscribe('onChange', () => {
			if (captchaTypeForm.fields[0].getValue() === 'disabled')
			{
				this.hideKeysCheckbox();
				this.hideKeysForm();
			}
			else
			{
				this.showKeysCheckbox();
				this.getKeysCheckbox().fields[0].setValue([hasKeys ? 'useCustomKeys' : '']);
				if (hasKeys)
				{
					this.showKeysForm();
				}
				else
				{
					this.hideKeysForm();
				}
			}
		});

		this.getKeysCheckbox().subscribe('onChange', () => {
			if (this.getKeysCheckbox().fields[0].getValue().includes('useCustomKeys'))
			{
				this.showKeysForm();
			}
			else
			{
				this.hideKeysForm();
			}
		});
	}

	getKeysCheckbox()
	{
		return this.cache.remember('keysCheckbox', () => {
			return new FormSettingsForm({
				id: 'customKeys',
				description: null,
				fields: [
					new BX.Landing.UI.Field.Checkbox({
						selector: 'useCustomKeys',
						items: [
							{
								name: Loc.getMessage('LANDING_SPAM_PROTECTION_CUSTOM_KEYS_CHECKBOX_LABEL'),
								value: 'useCustomKeys',
							},
						],
					}),
				],
			});
		});
	}

	showKeysCheckbox()
	{
		this.getKeysCheckbox().getLayout().hidden = false;
	}

	hideKeysCheckbox()
	{
		this.getKeysCheckbox().getLayout().hidden = true;
	}

	getKeysForm(): FormSettingsForm
	{
		return this.cache.remember('keysForm', () => {
			return new FormSettingsForm({
				id: 'keys',
				title: Loc.getMessage('LANDING_SPAM_PROTECTION_KEYS_FORM_TITLE'),
				help: {
					text: Loc.getMessage('LANDING_SPAM_PROTECTION_KEYS_FORM_HELP_TEXT'),
					href: Loc.getMessage('LANDING_SPAM_PROTECTION_KEYS_FORM_HELP_HREF'),
				},
				fields: [
					new TextField({
						selector: 'key',
						title: Loc.getMessage('LANDING_SPAM_PROTECTION_RECAPTCHA_KEY_FIELD_TITLE'),
						textOnly: true,
						content: this.options.values.key,
					}),
					new TextField({
						selector: 'secret',
						title: Loc.getMessage('LANDING_SPAM_PROTECTION_RECAPTCHA_SECRET_KEY_FIELD_TITLE'),
						textOnly: true,
						content: this.options.values.secret,
					}),
				],
			});
		});
	}

	showKeysForm()
	{
		this.getKeysForm().getLayout().hidden = false;
	}

	hideKeysForm()
	{
		this.getKeysForm().getLayout().hidden = true;
	}

	// eslint-disable-next-line class-methods-use-this
	valueReducer(sourceValue: {[p: string]: any}): {[p: string]: any}
	{
		const useCustomKeys = sourceValue.useCustomKeys.length > 0;

		return {
			recaptcha: {
				use: sourceValue.use === 'hidden',
				key: useCustomKeys ? sourceValue.key : '',
				secret: useCustomKeys ? sourceValue.secret : '',
			},
		};
	}

	onChange(event: BaseEvent)
	{
		this.emit('onChange', {...event.getData(), skipPrepare: true});
	}
}