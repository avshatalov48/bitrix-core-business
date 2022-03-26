import {Text} from 'main.core';
import {BaseEvent} from 'main.core.events';
import {Loc} from 'landing.loc';
import {ContentWrapper} from 'landing.ui.panel.basepresetpanel';
import {FormSettingsForm} from 'landing.ui.form.formsettingsform';
import {HeaderCard} from 'landing.ui.card.headercard';
import {TextField} from 'landing.ui.field.textfield';
import {MessageCard} from 'landing.ui.card.messagecard';

export default class Callback extends ContentWrapper
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.Callback');

		this.addItem(this.getHeader());

		if (!this.isAvailable())
		{
			this.addItem(this.getWarningMessage());
			this.getSettingsForm().disable();
		}

		this.addItem(this.getSettingsForm());
	}

	getHeader(): HeaderCard
	{
		return this.cache.remember('header', () => {
			return new HeaderCard({
				title: Loc.getMessage('LANDING_FORM_SETTINGS_CALLBACK_TITLE'),
			});
		});
	}

	getWarningMessage()
	{
		return this.cache.remember('warningMessage', () => {
			return new MessageCard({
				header: Loc.getMessage('LANDING_FORM_CALLBACK_WARNING_HEADER'),
				description: Loc.getMessage('LANDING_FORM_CALLBACK_WARNING_TEXT'),
				angle: false,
				closeable: false,
				hideActions: true,
				context: 'warning',
			});
		});
	}

	isAvailable(): boolean
	{
		return this.cache.remember('isAvailable', () => {
			return this.options.dictionary.callback.from.length > 0;
		});
	}

	getSettingsForm(): FormSettingsForm
	{
		return this.cache.remember('settingsForm', () => {
			return new FormSettingsForm({
				title: Loc.getMessage('LANDING_FORM_SETTINGS_CALLBACK_USE_CHECKBOX_LABEL'),
				toggleable: true,
				toggleableType: FormSettingsForm.ToggleableType.Switch,
				opened: this.isAvailable() && Text.toBoolean(this.options.formOptions.callback.use),
				fields: [
					this.getPhoneListField(),
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
				value: [Text.toBoolean(this.options.formOptions.callback.use)],
				items: [
					{name: Loc.getMessage('LANDING_FORM_SETTINGS_CALLBACK_USE_CHECKBOX_LABEL'), value: true},
				],
			});
		});
	}

	getPhoneListField(): BX.Landing.UI.Field.Dropdown
	{
		return this.cache.remember('phoneListField', () => {
			return new BX.Landing.UI.Field.Dropdown({
				selector: 'from',
				title: Loc.getMessage('LANDING_FORM_SETTINGS_CALLBACK_PHONE_TITLE'),
				content: this.options.formOptions.callback.from,
				items: [
					{name: Loc.getMessage('LANDING_FORM_DEFAULT_PHONE_NOT_SELECTED'), value: ''},
					...this.options.dictionary.callback.from.map((item) => {
						return {name: item.name, value: item.id};
					}),
				],
			});
		});
	}

	getTextField()
	{
		return this.cache.remember('textField', () => {
			return new TextField({
				selector: 'text',
				title: Loc.getMessage('LANDING_FORM_SETTINGS_CALLBACK_TEXT_TITLE'),
				content: this.options.formOptions.callback.text,
				textOnly: true,
			});
		});
	}

	valueReducer(value: {[key: string]: any}): {[key: string]: any}
	{
		return {
			callback: {
				...value,
				use: this.getSettingsForm().isOpened(),
			},
		};
	}

	onChange(event: BaseEvent)
	{
		this.emit('onChange', {...event.getData(), skipPrepare: true});
	}
}