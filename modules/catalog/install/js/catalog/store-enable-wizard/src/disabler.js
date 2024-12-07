import { Text, Loc, Extension, Runtime, Type } from 'main.core';
import { ModeList } from './mode-list';
import { Popup } from './popup';
import { Service } from './service';

type DisablerOptions = {
	events: {
		onDisabled: ?() => void,
	}
}

class Disabler
{
	#popup: Popup;
	#options: DisablerOptions;

	constructor(options: DisablerOptions = {})
	{
		this.#options = options;

		const hasCriticalErrors = this.#getPopupTexts().some((text) => text.critical === true);

		this.#popup = new Popup({
			helpCode: this.#getSetting('availableModes').length > 1 ? '20233748' : '15992592',
			width: hasCriticalErrors ? null : 'auto',
			title: this.#getPopupTitle(),
			texts: this.#getPopupTexts(),
			hideHelp: !hasCriticalErrors,
			hideIcon: !hasCriticalErrors,
			primaryButtonText: hasCriticalErrors
				? Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_POPUP_BUTTON_TURN_OFF_ANYWAY')
				: Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_POPUP_BUTTON_TURN_OFF'),
			primaryButtonClass: hasCriticalErrors ? 'ui-btn-danger' : 'ui-btn-primary',
			secondaryButtonText: Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_POPUP_BUTTON_CANCEL'),
			events: {
				onPrimaryClick: () => this.#disable(),
				onSecondaryClick: () => this.#popup.show(false),
				onClose: () => {},
			},
		});
	}

	open()
	{
		this.#popup.show(true);
	}

	sendDisableDoneEvent(status: string)
	{
		this.#sendEvent({
			tool: 'inventory',
			category: 'settings',
			event: 'disable_done',
			c_section: 'settings',
			p1: `mode_${this.#getSetting('currentMode')}`,
			status,
		});
	}

	#getPopupTitle(): String
	{
		if (this.#getSetting('currentMode') === ModeList.MODE_B24)
		{
			if (this.#getSetting('isWithOrdersMode') === true)
			{
				return Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_NOT_ABLE_TO_TURN_BACK_TITLE')
					.replace('[break]', '<br>');
			}

			if (this.#hasConductedDocumentsOrQuantities())
			{
				return Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_DATA_WILL_BE_DELETED_2')
					.replace('[break]', '<br>');
			}
		}

		return '';
	}

	#getPopupTexts(): Array
	{
		const result = [];

		if (this.#getSetting('currentMode') === ModeList.MODE_B24)
		{
			if (this.#hasConductedDocumentsOrQuantities())
			{
				result.push(
					{
						critical: true,
						text: Loc.getMessage(
							'CATALOG_INVENTORY_MANAGEMENT_DELETE_DOCUMENTS_AND_QUANTITY_TEXT_ON_DISABLE_B24_TEXT_1',
						),
					},
					{
						critical: true,
						text: Loc.getMessage(
							'CATALOG_INVENTORY_MANAGEMENT_DELETE_DOCUMENTS_AND_QUANTITY_TEXT_ON_DISABLE_B24_TEXT_2',
						),
					},
				);
			}

			if (this.#getSetting('isWithOrdersMode') === true)
			{
				result.push({
					critical: true,
					text: Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_NOT_ABLE_TO_TURN_BACK_TEXT')
						.replace('[break]', '<br>'),
				});
			}
		}

		if (result.length === 0)
		{
			result.push({
				text: Loc.getMessage(
					'CATALOG_INVENTORY_MANAGEMENT_DISABLE_CONFIRMATION_TEXT',
				),
			});
		}

		return result;
	}

	#disable()
	{
		this.#popup.load(true);

		Service.disable()
			.then(() => {
				this.sendDisableDoneEvent('success');
				this.#options.events?.onDisabled?.();
			})
			.catch((error) => {
				this.sendDisableDoneEvent(
					`error_${error?.customData?.analyticsCode ?? 'unknown'}`,
				);
				top.BX.UI.Notification.Center.notify({ content: Text.encode(error.message) });
			})
			.finally(() => {
				this.#popup.load(false);
				this.#popup.show(false);
			});
	}

	#getSetting(name: string)
	{
		return Extension.getSettings('catalog.store-enable-wizard').get(name);
	}

	#sendEvent(data: Object)
	{
		Runtime.loadExtension('ui.analytics')
			.then((exports) => {
				const { sendData } = exports;

				sendData(data);
			});
	}

	#hasConductedDocumentsOrQuantities(): boolean
	{
		if (Type.isBoolean(this.#options.hasConductedDocumentsOrQuantities))
		{
			return this.#options.hasConductedDocumentsOrQuantities;
		}

		return true;
	}
}

export {
	Disabler,
};
