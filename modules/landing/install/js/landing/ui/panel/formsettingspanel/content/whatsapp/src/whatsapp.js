import {Dom, Text, Type} from 'main.core';
import {BaseEvent} from 'main.core.events';
import {Loc} from 'landing.loc';
import {ContentWrapper} from 'landing.ui.panel.basepresetpanel';
import {FormSettingsForm} from 'landing.ui.form.formsettingsform';
import {HeaderCard} from 'landing.ui.card.headercard';
import {TextField} from 'landing.ui.field.textfield';
import {MessageCard} from 'landing.ui.card.messagecard';
import {FormClient} from 'crm.form.client';
import {FormSettingsPanel} from 'landing.ui.panel.formsettingspanel';

export default class Whatsapp extends ContentWrapper
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.Whatsapp');

		this.addItem(this.getHeader());

		if (this.options.dictionary.whatsapp.setup.completed)
		{
			this.addItem(this.getSettingsForm());
		}
		else
		{
			this.addItem(this.getWarningMessage());
		}
	}

	getHeader(): HeaderCard
	{
		return this.cache.remember('header', () => {
			return new HeaderCard({
				title: Loc.getMessage('LANDING_FORM_SETTINGS_WHATSAPP_TITLE'),
			});
		});
	}

	getWarningMessage()
	{
		return this.cache.remember('warningMessage', () => {
			return new MessageCard({
				header: Loc.getMessage('LANDING_FORM_WHATSAPP_WARNING_HEADER'),
				description: Loc.getMessage('LANDING_FORM_WHATSAPP_WARNING_TEXT'),
				angle: false,
				closeable: false,
				more: () => {
					BX.SidePanel.Instance.open(
						this.options.dictionary.whatsapp.setup.link,
						{
							events: {
								onClose: () => {
									FormClient
										.getInstance()
										.getDictionary()
										.then((dictionary) => {
											this.options.dictionary = dictionary;
											FormSettingsPanel.getInstance().setFormDictionary(dictionary);

											this.clear();

											this.addItem(this.getHeader());

											if (this.options.dictionary.whatsapp.setup.completed)
											{
												this.addItem(this.getSettingsForm());
											}
											else
											{
												this.addItem(this.getWarningMessage());
											}
										});
								},
							},
						},
					);
				},
			});
		});
	}

	getSettingsForm(): FormSettingsForm
	{
		return this.cache.remember('settingsForm', () => {
			return new FormSettingsForm({
				title: Loc.getMessage('LANDING_FORM_SETTINGS_WHATSAPP_USE_CHECKBOX_LABEL'),
				fields: [
					this.getTextField(),
				],
			});
		});
	}

	getUseCheckboxField(): BX.Landing.UI.Field.Checkbox
	{
		return this.cache.remember('useCheckboxField', () => {
			return new BX.Landing.UI.Field.Checkbox({
				selector: 'use',
				compact: true,
				value: [Text.toBoolean(this.options.formOptions.whatsapp.use)],
				items: [
					{name: Loc.getMessage('LANDING_FORM_SETTINGS_CALLBACK_USE_CHECKBOX_LABEL'), value: true},
				],
			});
		});
	}

	getTextField()
	{
		return this.cache.remember('textField', () => {
			const textItem = this.options.dictionary.whatsapp.messages.find((item) => {
				return String(item.langId) === String(this.options.data.language);
			});
			const text = Type.isPlainObject(textItem) ? textItem.text : '';

			const field = new TextField({
				selector: 'text',
				title: Loc.getMessage('LANDING_FORM_SETTINGS_WHATSAPP_TEXT_TITLE'),
				content: text,
				textOnly: true,
			});

			Dom.addClass(field.input, 'landing-ui-disabled');

			return field;
		});
	}

	valueReducer(value: {[key: string]: any}): {[key: string]: any}
	{
		return {
			whatsapp: {
				use: this.getSettingsForm().isOpened(),
			},
		};
	}

	onChange(event: BaseEvent)
	{
		this.emit('onChange', {...event.getData(), skipPrepare: true});
	}
}