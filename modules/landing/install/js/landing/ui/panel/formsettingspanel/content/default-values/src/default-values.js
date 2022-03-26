import {ContentWrapper} from 'landing.ui.panel.basepresetpanel';
import {FormSettingsForm} from 'landing.ui.form.formsettingsform';
import {DefaultValueField} from 'landing.ui.field.defaultvaluefield';
import {HeaderCard} from 'landing.ui.card.headercard';
import {Loc} from 'landing.loc';
import {Dom, Type} from 'main.core';
import {MessageCard} from 'landing.ui.card.messagecard';

import './css/style.css';

export default class DefaultValues extends ContentWrapper
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.DefaultValues');

		const header = new HeaderCard({
			title: Loc.getMessage('LANDING_FORM_SETTINGS_DEFAULT_VALUE_TITLE'),
		});

		const message = new MessageCard({
			id: 'defaultValueMessage',
			header: Loc.getMessage('LANDING_FORM_SETTINGS_DEFAULT_VALUE_MESSAGE_TITLE'),
			description: Loc.getMessage('LANDING_FORM_SETTINGS_DEFAULT_VALUE_MESSAGE_DESCRIPTION'),
			restoreState: true,
		});

		const fieldsForm = new FormSettingsForm({
			fields: [
				new DefaultValueField({
					selector: 'presetFields',
					isLeadEnabled: this.options.isLeadEnabled,
					personalizationVariables: this.getPersonalizationVariables(),
					formOptions: {
						...this.options.formOptions,
					},
					crmFields: {
						...this.options.crmFields,
					},
					dictionary: {
						...this.options.dictionary,
					},
					items: [
						...this.options.formOptions.presetFields,
					],
				}),
			],
		});

		if (!message.isShown())
		{
			fieldsForm.setOffsetTop(-36);
		}

		message.subscribe('onClose', () => {
			fieldsForm.setOffsetTop(-36);
		});

		this.addItem(header);
		this.addItem(message);
		this.addItem(fieldsForm);
	}

	getPersonalizationVariables(): Array<{name: string, value: string}>
	{
		return this.cache.remember('personalizationVariables', () => {
			const {properties} = this.options.dictionary;
			if (Type.isPlainObject(properties) && Type.isArrayFilled(properties.list))
			{
				return properties.list.map((item) => {
					return {name: item.name, value: item.id};
				});
			}

			return [];
		});
	}

	getLayout(): HTMLDivElement
	{
		const layout = super.getLayout();
		Dom.addClass(layout, 'landing-ui-default-fields-values');

		return layout;
	}

	onChange(event: BaseEvent)
	{
		this.emit('onChange', {...event.getData(), skipPrepare: true});
	}
}