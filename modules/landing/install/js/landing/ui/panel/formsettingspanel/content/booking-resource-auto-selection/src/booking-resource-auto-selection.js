import { Reflection, Tag } from 'main.core';
import { BaseEvent } from 'main.core.events';
import { CrmFormSettingsDataPropName } from 'booking.const';

import { Loc } from 'landing.loc';
import { HeaderCard } from 'landing.ui.card.headercard';
import { MessageCard } from 'landing.ui.card.messagecard';
import { ContentWrapper } from 'landing.ui.panel.basepresetpanel';
import { FormSettingsForm } from 'landing.ui.form.formsettingsform';

import 'ui.sidepanel-content';

import resourceAutoSelectionIcon from './images/resource-auto-selection-icon.svg';

export default class BookingResourceAutoSelection extends ContentWrapper
{
	#options;

	constructor(options)
	{
		super(options);

		this.#options = options;

		this.setEventNamespace('BX.Landing.UI.Panel.FormSettingsPanel.BookingResourceAutoSelectionContent');

		this.addItem(this.#getHeaderCard());
		this.addItem(this.#getMessageCard());
		this.addItem(this.#getSettingsForm());
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember(
			'layout',
			() => Tag.render`<div class="landing-ui-content-booking-resource-auto-selection"></div>`,
		);
	}

	valueReducer(value): { bookingResourceAutoSelection: { use: boolean } }
	{
		const checked = Boolean(this.#getSettingsForm().isOpened());

		return {
			data: this.#getFormOptionData(checked),
			bookingResourceAutoSelection: {
				...value,
				use: checked,
			},
		};
	}

	onChange(event: BaseEvent): void
	{
		this.emit('onChange', { ...event.getData(), skipPrepare: true });
	}

	#getFormOptionData(checked: boolean): Array
	{
		const bookingField = this.#options?.formOptions?.data?.fields?.find(({ id }) => id === 'BOOKING_BOOKING');
		if (!bookingField)
		{
			return this.#options?.formOptions?.data;
		}

		const settingsData = bookingField.settingsData || {};
		settingsData[CrmFormSettingsDataPropName.isAutoSelectionOn] = checked;
		settingsData[CrmFormSettingsDataPropName.autoSelection] = Object.assign(
			(settingsData[CrmFormSettingsDataPropName.autoSelection] || {}),
			{ hasSlotsAllAvailableResources: false },
		);

		bookingField.settingsData = {
			...settingsData,
		};

		return this.#options.formOptions.data;
	}

	#getHeaderCard(): HeaderCard
	{
		return new HeaderCard({
			title: Loc.getMessage('LANDING_FORM_BOOKING_RESOURCE_AUTO_SELECTION_TITLE'),
		});
	}

	#getMessageCard(): MessageCard
	{
		return new MessageCard({
			id: 'bookingResourceAutoSelectionMessage',
			header: Loc.getMessage('LANDING_FORM_BOOKING_RESOURCE_AUTO_SELECTION_MESSAGE_HEADER'),
			description: Loc.getMessage('LANDING_FORM_BOOKING_RESOURCE_AUTO_SELECTION_MESSAGE_DESCRIPTION'),
			restoreState: true,
			closeable: false,
			angle: false,
			more: () => {
				const helper = Reflection.getClass('top.BX.Helper');
				if (helper)
				{
					BX.Helper.show('redirect=detail&code=25366370');
				}
			},
			icon: resourceAutoSelectionIcon,
		});
	}

	#getSettingsForm(): FormSettingsForm
	{
		return this.cache.remember('bookingResourceAutoSelectionForm', () => {
			return new FormSettingsForm({
				id: 'bookingResourceAutoSelection',
				title: Loc.getMessage('LANDING_FORM_BOOKING_RESOURCE_AUTO_SELECTION_SELECTOR'),
				toggleable: true,
				opened: this.options.formOptions.bookingResourceAutoSelection.use,
				fields: [],
			});
		});
	}
}
