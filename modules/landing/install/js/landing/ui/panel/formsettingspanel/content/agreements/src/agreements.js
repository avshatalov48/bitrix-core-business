import {Dom} from 'main.core';
import {Loc} from 'landing.loc';
import {HeaderCard} from 'landing.ui.card.headercard';
import {MessageCard} from 'landing.ui.card.messagecard';
import {FormSettingsForm} from 'landing.ui.form.formsettingsform';
import {AgreementsList} from 'landing.ui.field.agreementslist';
import {ContentWrapper} from 'landing.ui.panel.basepresetpanel';
import messageIcon from './images/message-icon.svg';

export default class AgreementsContent extends ContentWrapper
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.AgreementsContent');

		const header = new HeaderCard({
			title: Loc.getMessage('LANDING_AGREEMENTS_TITLE'),
		});

		const message = new MessageCard({
			id: 'agreementsMessage',
			header: Loc.getMessage('LANDING_AGREEMENTS_MESSAGE_HEADER'),
			description: Loc.getMessage('LANDING_AGREEMENTS_MESSAGE_DESCRIPTION'),
			icon: messageIcon,
			restoreState: true,
		});

		const listForm = new FormSettingsForm({
			id: 'agreementsList',
			fields: [
				new AgreementsList({
					selector: 'agreements',
					formOptions: this.options.formOptions,
					agreementsList: this.options.agreements,
					value: this.options.formOptions.data.agreements,
				}),
			],
		});

		if (!message.isShown())
		{
			Dom.style(header.getLayout(), 'margin-bottom', '0');
			Dom.style(listForm.getLayout(), 'margin-top', '-19px');
		}

		message.subscribe('onClose', () => {
			Dom.style(header.getLayout(), 'margin-bottom', '0');
			Dom.style(listForm.getLayout(), 'margin-top', '-19px');
		});

		this.addItem(header);
		this.addItem(message);
		this.addItem(listForm);
	}
}