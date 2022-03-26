import {Loc} from 'landing.loc';
import {HeaderCard} from 'landing.ui.card.headercard';
import {MessageCard} from 'landing.ui.card.messagecard';
import {ContentWrapper} from 'landing.ui.panel.basepresetpanel';
import {FormSettingsForm} from 'landing.ui.form.formsettingsform';
import {FieldsListField} from 'landing.ui.field.fieldslistfield';
import messageIcon from './images/message-icon.svg';

export default class FieldsContent extends ContentWrapper
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.FieldsContent');

		const header = new HeaderCard({
			title: Loc.getMessage('LANDING_FORM_SETTINGS_FIELDS_TITLE'),
		});

		const message = new MessageCard({
			id: 'fieldsMessage',
			header: Loc.getMessage('LANDING_FORM_SETTINGS_FIELDS_MESSAGE_TITLE'),
			description: Loc.getMessage('LANDING_FORM_SETTINGS_FIELDS_MESSAGE_DESCRIPTION'),
			icon: messageIcon,
			restoreState: true,
		});

		const fieldsForm = new FormSettingsForm({
			fields: [
				new FieldsListField({
					selector: 'fields',
					isLeadEnabled: this.options.isLeadEnabled,
					dictionary: this.options.dictionary,
					formOptions: {
						...this.options.formOptions,
					},
					crmFields: {
						...this.options.crmFields,
					},
					items: [
						...this.options.formOptions.data.fields,
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
}