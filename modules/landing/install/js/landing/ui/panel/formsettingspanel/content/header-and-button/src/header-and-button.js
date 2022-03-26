import {HeaderCard} from 'landing.ui.card.headercard';
import {Loc, Reflection, Type} from 'main.core';
import {MessageCard} from 'landing.ui.card.messagecard';
import {FormSettingsForm} from 'landing.ui.form.formsettingsform';
import {TextField} from 'landing.ui.field.textfield';
import {VariablesField} from 'landing.ui.field.variablesfield';
import {ContentWrapper} from 'landing.ui.panel.basepresetpanel';
import 'helper';
import headerAndButtonsIcon from './images/header-and-buttons-message-icon.svg';

export default class HeaderAndButtonContent extends ContentWrapper
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.HeaderAndButtonContent');

		const header = new HeaderCard({
			title: Loc.getMessage('LANDING_HEADER_AND_BUTTONS_TITLE'),
		});

		const message = new MessageCard({
			id: 'headerAndButtonMessage',
			icon: headerAndButtonsIcon,
			header: Loc.getMessage('LANDING_HEADER_AND_BUTTONS_MESSAGE_HEADER'),
			description: Loc.getMessage('LANDING_HEADER_AND_BUTTONS_MESSAGE_DESCRIPTION_2'),
			restoreState: true,
			more: () => {
				const helper = Reflection.getClass('top.BX.Helper');
				if (helper)
				{
					BX.Helper.show('redirect=detail&code=12802786');
				}
			},
		});

		const headersForm = new FormSettingsForm({
			id: 'headers',
			title: Loc.getMessage('LANDING_HEADER_AND_BUTTONS_HEADERS_FORM_TITLE'),
			fields: [
				new VariablesField({
					selector: 'title',
					title: Loc.getMessage('LANDING_HEADER_AND_BUTTONS_HEADERS_FORM_HEADER_FIELD_TITLE'),
					textOnly: true,
					content: this.options.formOptions.data.title,
					variables: this.getPersonalizationVariables(),
				}),
				new VariablesField({
					selector: 'desc',
					title: Loc.getMessage('LANDING_HEADER_AND_BUTTONS_HEADERS_FORM_SUBHEADER_FIELD_TITLE'),
					textOnly: true,
					content: this.options.formOptions.data.desc,
					variables: this.getPersonalizationVariables(),
				}),
				new BX.Landing.UI.Field.Checkbox({
					selector: 'hideDesc',
					items: [
						{
							value: 'hideDesc',
							name: Loc.getMessage('LANDING_HEADER_AND_BUTTONS_HEADERS_FORM_HIDE_SUBHEADER_FIELD_TITLE'),
						},
						// {
						// 	value: 'hideSeparator',
						// 	name: Loc.getMessage('LANDING_HEADER_AND_BUTTONS_HEADERS_FORM_HIDE_SEPARATOR_FIELD_TITLE'),
						// },
					],
					compact: true,
				}),
			],
		});

		const buttonsForm = new FormSettingsForm({
			id: 'buttons',
			title: Loc.getMessage('LANDING_HEADER_AND_BUTTONS_BUTTONS_FORM_TITLE'),
			fields: [
				new TextField({
					selector: 'buttonCaption',
					title: Loc.getMessage('LANDING_HEADER_AND_BUTTONS_BUTTONS_FORM_SEND_BUTTON_TITLE'),
					textOnly: true,
					content: this.options.formOptions.data.buttonCaption,
				}),
			],
		});

		this.addItem(header);
		this.addItem(message);
		this.addItem(headersForm);
		this.addItem(buttonsForm);
	}

	getPersonalizationVariables(): Array<{name: string, value: string}>
	{
		return this.cache.remember('personalizationVariables', () => {
			return this.options.dictionary.personalization.list.map((item) => {
				return {name: item.name, value: item.id};
			});
		});
	}

	// eslint-disable-next-line class-methods-use-this
	valueReducer(sourceValue: {[p: string]: any}): {[p: string]: any}
	{
		const value = Object.entries(sourceValue).reduce((acc, [key, value]) => {
			if (key === 'hideDesc')
			{
				if (value.includes(key))
				{
					acc.desc = '';
				}

				delete acc.hideDesc;
			}

			if (key === 'useSign')
			{
				acc.useSign = value.includes('useSign');
			}

			return acc;
		}, {...sourceValue});

		if (!this.items[2].getSwitch().getValue())
		{
			value.title = '';
			value.desc = '';
		}

		return value;
	}

	onChange(event: BaseEvent)
	{
		this.emit('onChange', {...event.getData(), skipPrepare: true});
	}
}