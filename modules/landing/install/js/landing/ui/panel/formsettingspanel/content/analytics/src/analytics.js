import {Loc} from 'landing.loc';
import {HeaderCard} from 'landing.ui.card.headercard';
import {ContentWrapper} from 'landing.ui.panel.basepresetpanel';
import {AccordionField} from 'landing.ui.field.accordionfield';
import {MessageCard} from 'landing.ui.card.messagecard';

import yandexIcon from './images/yandex.svg';
import googleIcon from './images/google.svg';
import {ContentTable} from './internal/table/content-table';

export default class AnalyticsContent extends ContentWrapper
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.AgreementsContent');

		const header = new HeaderCard({
			title: Loc.getMessage('LANDING_FORM_SETTINGS_ANALYTICS_TITLE'),
		});

		const items = [];
		if (Loc.getMessage('LANGUAGE_ID') === 'ru')
		{
			items.push({
				id: 'yandex',
				title: Loc.getMessage('LANDING_FORM_SETTINGS_ANALYTICS_ITEM_YANDEX_METRIKA'),
				icon: yandexIcon,
				checked: true,
				switcher: false,
				content: this.getYandexTable(),
			});
		}

		items.push({
			id: 'google',
			title: Loc.getMessage('LANDING_FORM_SETTINGS_ANALYTICS_ITEM_GOOGLE_ANALYTICS'),
			icon: googleIcon,
			checked: true,
			switcher: false,
			content: this.getGoogleTable(),
		});

		const accordionField = new AccordionField({
			selector: 'analytics',
			title: Loc.getMessage('LANDING_FORM_SETTINGS_ANALYTICS_ITEMS_FIELD_TITLE'),
			items,
		});

		const message = new MessageCard({
			id: 'analyticsMessage',
			header: Loc.getMessage('LANDING_FORM_SETTINGS_ANALYTICS_MESSAGE_TITLE'),
			description: Loc.getMessage('LANDING_FORM_SETTINGS_ANALYTICS_MESSAGE_DESCRIPTION'),
			angle: false,
			restoreState: true,
		});

		this.addItem(header);
		this.addItem(message);
		this.addItem(accordionField);
	}

	getYandexTable(): HTMLTableElement
	{
		const table = new ContentTable({
			columns: [
				{
					id: 'title',
					content: Loc.getMessage('LANDING_FORM_SETTINGS_ANALYTICS_TABLE_NAME_COLUMN_TITLE'),
				},
				{
					id: 'id',
					content: Loc.getMessage('LANDING_FORM_SETTINGS_ANALYTICS_TABLE_ID_COLUMN_TITLE'),
				},
			],
			rows: this.options.formOptions.analytics.steps.map((row) => {
				return {
					columns: [
						{content: row.name},
						{content: row.event},
					],
				};
			}),
		});

		return table.render();
	}

	getGoogleTable(): HTMLTableElement
	{
		const table = new ContentTable({
			columns: [
				{
					id: 'title',
					content: Loc.getMessage('LANDING_FORM_SETTINGS_ANALYTICS_TABLE_NAME_COLUMN_TITLE'),
				},
				{
					id: 'id',
					content: Loc.getMessage('LANDING_FORM_SETTINGS_ANALYTICS_TABLE_ID_COLUMN_TITLE'),
				},
			],
			rows: this.options.formOptions.analytics.steps.map((row) => {
				return {
					columns: [
						{content: row.name},
						{content: row.code},
					],
				};
			}),
		});

		return table.render();
	}
}